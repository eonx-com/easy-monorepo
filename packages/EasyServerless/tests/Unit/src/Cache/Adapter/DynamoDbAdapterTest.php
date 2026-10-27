<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\src\Cache\Adapter;

use AsyncAws\Core\Credentials\NullProvider;
use AsyncAws\DynamoDb\DynamoDbClient;
use EonX\EasyServerless\Cache\Adapter\DynamoDbAdapter;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class DynamoDbAdapterTest extends AbstractUnitTestCase
{
    private const TABLE_NAME = 'cache_items';

    /**
     * @var list<array{RequestItems: array<string, list<array{PutRequest: array{Item: array<string, array<string, string>>}}>>}>
     */
    private array $batchWriteRequests = [];

    /**
     * @var array<string, array<string, array<string, string>>>
     */
    private array $items = [];

    private int $unprocessedBatchWrites = 0;

    public function testSaveSucceedsWhenDynamoDbReturnsUnprocessedItems(): void
    {
        $sut = $this->createAdapter();
        $this->unprocessedBatchWrites = 1;

        $result = $sut->save($sut->getItem('key')->set('value')->expiresAfter(60));

        self::assertTrue($result);
        self::assertCount(2, $this->batchWriteRequests);
        self::assertSame($this->batchWriteRequests[0], $this->batchWriteRequests[1]);
        self::assertSame([self::TABLE_NAME], \array_keys($this->batchWriteRequests[1]['RequestItems']));
        self::assertSame(
            'key',
            $this->batchWriteRequests[1]['RequestItems'][self::TABLE_NAME][0]['PutRequest']['Item']['key_id']['S']
        );
        self::assertSame('value', $sut->getItem('key')->get());
    }

    /**
     * @return array{Responses: array<string, list<array<string, array<string, string>>>>}
     */
    private function batchGetItem(string $body): array
    {
        /** @var array{RequestItems: array{cache_items: array{Keys: list<array{key_id: array{S: string}}>}}} $request */
        $request = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
        $items = [];

        foreach ($request['RequestItems'][self::TABLE_NAME]['Keys'] as $key) {
            $id = $key['key_id']['S'];

            if (isset($this->items[$id])) {
                $items[] = $this->items[$id];
            }
        }

        return [
            'Responses' => [
                self::TABLE_NAME => $items,
            ],
        ];
    }

    /**
     * @return array{UnprocessedItems?: array<string, list<array{PutRequest: array{Item: array<string, array<string, string>>}}>>}
     */
    private function batchWriteItem(string $body): array
    {
        /** @var array{RequestItems: array<string, list<array{PutRequest: array{Item: array{key_id: array{S: string}}&array<string, array<string, string>>}}>>} $request */
        $request = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
        $this->batchWriteRequests[] = $request;

        if ($this->unprocessedBatchWrites > 0) {
            $this->unprocessedBatchWrites--;

            return [
                'UnprocessedItems' => $request['RequestItems'],
            ];
        }

        foreach ($request['RequestItems'][self::TABLE_NAME] as $writeRequest) {
            $item = $writeRequest['PutRequest']['Item'];
            $this->items[$item['key_id']['S']] = $item;
        }

        return [];
    }

    private function createAdapter(): DynamoDbAdapter
    {
        $dynamoDbClient = new DynamoDbClient(
            [
                'endpoint' => 'http://dynamodb.test',
                'region' => 'ap-southeast-2',
            ],
            new NullProvider(),
            new MockHttpClient($this->handleDynamoDbRequest(...))
        );

        return new DynamoDbAdapter(dynamoDbClient: $dynamoDbClient);
    }

    /**
     * @param array{body: string, normalized_headers: array<string, list<string>>} $options
     */
    private function handleDynamoDbRequest(string $method, string $url, array $options): MockResponse
    {
        $target = \substr($options['normalized_headers']['x-amz-target'][0], \strlen('X-Amz-Target: '));

        $response = match ($target) {
            'DynamoDB_20120810.BatchGetItem' => $this->batchGetItem($options['body']),
            'DynamoDB_20120810.BatchWriteItem' => $this->batchWriteItem($options['body']),
            default => self::fail(\sprintf('Unexpected DynamoDB request "%s".', $target)),
        };

        return new MockResponse(\json_encode($response, \JSON_THROW_ON_ERROR));
    }
}
