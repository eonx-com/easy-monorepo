<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Cache\Adapter;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Input\BatchGetItemInput;
use AsyncAws\DynamoDb\Input\BatchWriteItemInput;
use AsyncAws\DynamoDb\Input\GetItemInput;
use AsyncAws\DynamoDb\Input\ScanInput;
use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use AsyncAws\DynamoDb\ValueObject\DeleteRequest;
use AsyncAws\DynamoDb\ValueObject\KeysAndAttributes;
use AsyncAws\DynamoDb\ValueObject\PutRequest;
use AsyncAws\DynamoDb\ValueObject\WriteRequest;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

final class DynamoDbAdapter extends AbstractAdapter
{
    private const DEFAULT_OPTIONS = [
        'data_attr' => 'key_data',
        'expiration_attr' => 'key_expiration',
        'id_attr' => 'key_id',
        'table_name' => 'cache_items',
    ];

    private string $dataAttr;

    private string $expirationAttr;

    private string $idAttr;

    private string $tableName;

    public function __construct(
        private readonly DynamoDbClient $dynamoDbClient = new DynamoDbClient(),
        private readonly MarshallerInterface $marshaller = new DefaultMarshaller(),
        ?array $options = null,
        ?string $namespace = null,
        ?int $defaultLifetime = null,
    ) {
        parent::__construct($namespace ?? '', $defaultLifetime ?? 0);

        $this->dataAttr = $options['data_attr'] ?? self::DEFAULT_OPTIONS['data_attr'];
        $this->expirationAttr = $options['expiration_attr'] ?? self::DEFAULT_OPTIONS['expiration_attr'];
        $this->idAttr = $options['id_attr'] ?? self::DEFAULT_OPTIONS['id_attr'];
        $this->tableName = $options['table_name'] ?? self::DEFAULT_OPTIONS['table_name'];
    }

    protected function doClear(string $namespace): bool
    {
        $input = new ScanInput([
            'ConsistentRead' => true,
            'TableName' => $this->tableName,
        ]);

        if ($namespace !== '') {
            $input->setFilterExpression('begins_with(#idAttr, :namespace)');
            $input->setExpressionAttributeNames(['#idAttr' => $this->idAttr]);
            $input->setExpressionAttributeValues([':namespace' => new AttributeValue(['S' => $namespace])]);
        }

        do {
            $response = $this->dynamoDbClient->scan($input);
            $ids = [];

            foreach ($response->getItems(true) as $item) {
                $id = $item[$this->idAttr]?->getS();

                if ($id !== null) {
                    $ids[] = $id;
                }
            }

            if (\count($ids) > 0) {
                $this->doDelete($ids);
            }
        } while (\count($response->getLastEvaluatedKey()) > 0);

        return true;
    }

    protected function doDelete(array $ids): bool
    {
        $requestItems = null;

        do {
            if ($requestItems === null) {
                $requestItems = [
                    $this->tableName => \array_map(function (string $id): WriteRequest {
                        return new WriteRequest([
                            'DeleteRequest' => new DeleteRequest([
                                'Key' => [
                                    $this->idAttr => new AttributeValue(['S' => $id]),
                                ],
                            ]),
                        ]);
                    }, $ids),
                ];
            }

            $response = $this->dynamoDbClient->batchWriteItem(new BatchWriteItemInput([
                'RequestItems' => $requestItems,
            ]));

            // If there are unprocessed items, we need to retry them
            $requestItems = $response->getUnprocessedItems();
            $hasUnprocessedItems = \count($requestItems) > 0;
        } while ($hasUnprocessedItems);

        return true;
    }

    /**
     * @throws \Exception
     */
    protected function doFetch(array $ids): iterable
    {
        if (\count($ids) < 1) {
            return [];
        }

        $input = new BatchGetItemInput([
             'RequestItems' => [
                 $this->tableName => new KeysAndAttributes([
                     'ConsistentRead' => true,
                     'Keys' => \array_map(function (string $id): array {
                        return [
                            $this->idAttr => new AttributeValue(['S' => $id]),
                        ];
                     }, $ids)
                 ]),
             ],
        ]);

        $response = $this->dynamoDbClient->batchGetItem($input);

        foreach ($response->getResponses() as $tableName => $items) {
            if ($tableName !== $this->tableName) {
                continue;
            }

            foreach ($items as $item) {
                $idAttr = $item[$this->idAttr]?->getS() ?? null;
                $dataAttr = $item[$this->dataAttr]?->getS() ?? null;
                $expirationAttr = $item[$this->expirationAttr]?->getN() ?? null;

                // Skip item if expired, dynamodb will remove it automatically
                if ($expirationAttr !== null && ((float) $expirationAttr) < \microtime(true)) {
                    continue;
                }

                if ($idAttr !== null && $dataAttr !== null) {
                    // Decoding the data attribute from base64
                    $dataAttr = \base64_decode(\strtr($dataAttr, '._', '/+'), true);

                    yield $idAttr => $this->marshaller->unmarshall($dataAttr);
                }
            }
        }
    }

    protected function doHave(string $id): bool
    {
        $existingCacheItem = $this->dynamoDbClient->getItem(new GetItemInput([
            'ConsistentRead' => true,
            'Key' => [
                $this->idAttr => new AttributeValue(['S' => $id]),
            ],
            'TableName' => $this->tableName,
        ]));

        $item = $existingCacheItem->getItem();

        // Item wasn't found at all
        if ($item === []) {
            return false;
        }

        $expirationAttr = $item[$this->expirationAttr]?->getN() ?? null;

        // Treat expired items as not existing
        return $expirationAttr !== null && ((float) $expirationAttr) > \microtime(true);
    }

    protected function doSave(array $values, int $lifetime): array|bool
    {
        $values = $this->marshaller->marshall($values, $failed);

        if (\is_array($failed) && \count($failed) > 0) {
            return $failed;
        }

        $writeRequests = null;

        do {
            if ($writeRequests === null) {
                $writeRequests = [];

                foreach ($values as $id => $data) {
                    $expiration = \microtime(true) + $lifetime;

                    // Prevent malformed utf-8 data error from DynamoDB
                    $data = \strtr(\base64_encode($data), '/+', '._');

                    $writeRequests[] = new WriteRequest([
                        'PutRequest' => new PutRequest([
                            'Item' => [
                                $this->idAttr => new AttributeValue(['S' => $id]),
                                $this->dataAttr => new AttributeValue(['S' => $data]),
                                $this->expirationAttr => new AttributeValue(['N' => (string) $expiration]),
                            ],
                        ]),
                    ]);
                }
            }

            $response = $this->dynamoDbClient->batchWriteItem(new BatchWriteItemInput([
                'RequestItems' => [
                    $this->tableName => $writeRequests,
                ],
            ]));

            // If there are unprocessed items, we need to retry them
            $writeRequests = $response->getUnprocessedItems();
            $hasUnprocessedItems = \count($writeRequests) > 0;
        } while ($hasUnprocessedItems);

        return true;
    }
}
