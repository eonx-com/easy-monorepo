<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\DynamoDb\Store;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Input\GetItemInput;
use AsyncAws\DynamoDb\Input\PutItemInput;
use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use Carbon\CarbonImmutable;
use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface;
use EonX\EasyWebhook\Common\Store\AbstractStore;

abstract class AbstractDynamoDbStore extends AbstractStore
{
    public const string DEFAULT_TTL_ATTRIBUTE = 'ttl';

    public function __construct(
        RandomGeneratorInterface $random,
        DataCleanerInterface $dataCleaner,
        private readonly DynamoDbClient $dynamoDbClient,
        private readonly ?string $table = null,
        private readonly ?string $ttl = null,
        private readonly ?string $ttlAttribute = null,
    ) {
        parent::__construct($random, $dataCleaner);
    }

    /**
     * @param \AsyncAws\DynamoDb\ValueObject\AttributeValue[]|null $previousData
     *
     * @return \AsyncAws\DynamoDb\ValueObject\AttributeValue[]
     */
    abstract protected function createItemFromInstance(object $instance, ?array $previousData = null): array;

    abstract protected function getDefaultTable(): string;

    /**
     * @param \AsyncAws\DynamoDb\ValueObject\AttributeValue[] $item
     */
    abstract protected function instantiateFromResultItem(array $item): object;

    protected function doFind(string $keyName, string $keyValue, ?bool $consistentRead = null): ?object
    {
        $result = $this->doFindRaw($keyName, $keyValue, $consistentRead);

        return \is_array($result) ? $this->instantiateFromResultItem($result) : null;
    }

    /**
     * @return \AsyncAws\DynamoDb\ValueObject\AttributeValue[]|null
     */
    protected function doFindRaw(string $keyName, string $keyValue, ?bool $consistentRead = null): ?array
    {
        $result = $this->dynamoDbClient->getItem(new GetItemInput([
            'ConsistentRead' => $consistentRead ?? false,
            'Key' => [
                $keyName => AttributeValue::create(['S' => $keyValue]),
            ],
            'TableName' => $this->table ?? $this->getDefaultTable(),
        ]));

        $item = $result->getItem();

        return \count($item) > 0 ? $item : null;
    }

    /**
     * @param \AsyncAws\DynamoDb\ValueObject\AttributeValue[]|null $previousData
     */
    protected function doPutItem(object $instance, ?string $uniqueAttr = null, ?array $previousData = null): object
    {
        $item = $this->createItemFromInstance($instance, $previousData);

        if (\is_string($this->ttl) && $this->ttl !== '') {
            $ttlAttr = $this->ttlAttribute ?? self::DEFAULT_TTL_ATTRIBUTE;
            $item[$ttlAttr] = AttributeValue::create([
                'N' => (string)CarbonImmutable::now('UTC')->add($this->ttl)->getTimestamp(),
            ]);
        }

        $input = [
            'Item' => $item,
            'TableName' => $this->table ?? $this->getDefaultTable(),
        ];

        if ($uniqueAttr !== null && $uniqueAttr !== '') {
            $input['ConditionExpression'] = \sprintf('attribute_not_exists(%s)', $uniqueAttr);
        }

        $this->dynamoDbClient->putItem(new PutItemInput($input))
            ->getAttributes();

        return $instance;
    }
}
