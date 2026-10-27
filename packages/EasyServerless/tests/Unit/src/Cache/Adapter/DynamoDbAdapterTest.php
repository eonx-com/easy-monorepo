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
    private const string TABLE_NAME = 'cache_items';

    /**
     * @var array<string, array<string, array<string, string>>>
     */
    private array $items = [];

    public function testGetItemSkipsExpiredItem(): void
    {
        $sut = $this->createAdapter();
        $sut->save($sut->getItem('key')->set('value')->expiresAfter(60));
        $this->items['key']['key_expiration'] = ['N' => (string)(\microtime(true) - 1)];

        $item = $sut->getItem('key');

        self::assertFalse($item->isHit());
        self::assertFalse($sut->hasItem('key'));
    }

    public function testGetItemSucceedsWhenItemHasNoExpiration(): void
    {
        $sut = $this->createAdapter();
        $sut->save($sut->getItem('key')->set('value')->expiresAfter(60));
        unset($this->items['key']['key_expiration']);

        $item = $sut->getItem('key');

        self::assertTrue($item->isHit());
        self::assertSame('value', $item->get());
        self::assertTrue($sut->hasItem('key'));
    }

    public function testSaveSucceedsWithPositiveLifetime(): void
    {
        $sut = $this->createAdapter();
        $before = \microtime(true);

        $result = $sut->save($sut->getItem('key')->set('value')->expiresAfter(60));

        self::assertTrue($result);
        self::assertArrayHasKey('key_expiration', $this->items['key']);
        self::assertEqualsWithDelta($before + 60, (float)$this->items['key']['key_expiration']['N'], 2.0);
        self::assertSame('value', $sut->getItem('key')->get());
        self::assertTrue($sut->hasItem('key'));
    }

    public function testSaveSucceedsWithZeroLifetime(): void
    {
        $sut = $this->createAdapter();

        $result = $sut->save($sut->getItem('key')->set('value'));

        self::assertTrue($result);
        self::assertArrayHasKey('key', $this->items);
        self::assertArrayNotHasKey('key_expiration', $this->items['key']);
        $item = $sut->getItem('key');
        self::assertTrue($item->isHit());
        self::assertSame('value', $item->get());
        self::assertTrue($sut->hasItem('key'));
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
     * @return array{}
     */
    private function batchWriteItem(string $body): array
    {
        /** @var array{RequestItems: array{cache_items: list<array{PutRequest: array{Item: array{key_id: array{S: string}}&array<string, array<string, string>>}}>}} $request */
        $request = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);

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
     * @return array{Item?: array<string, array<string, string>>}
     */
    private function getItem(string $body): array
    {
        /** @var array{Key: array{key_id: array{S: string}}} $request */
        $request = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
        $id = $request['Key']['key_id']['S'];

        return isset($this->items[$id]) ? ['Item' => $this->items[$id]] : [];
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
            'DynamoDB_20120810.GetItem' => $this->getItem($options['body']),
            default => self::fail(\sprintf('Unexpected DynamoDB request "%s".', $target)),
        };

        return new MockResponse(\json_encode($response, \JSON_THROW_ON_ERROR));
    }
}
