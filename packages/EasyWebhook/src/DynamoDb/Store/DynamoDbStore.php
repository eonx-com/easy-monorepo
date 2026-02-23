<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\DynamoDb\Store;

use AsyncAws\DynamoDb\Exception\ConditionalCheckFailedException;
use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use Carbon\CarbonImmutable;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;

final class DynamoDbStore extends AbstractDynamoDbStore implements StoreInterface
{
    public const string DEFAULT_TABLE = 'easy_webhooks';

    public function find(string $id): ?WebhookInterface
    {
        /** @var \EonX\EasyWebhook\Common\Entity\WebhookInterface|null $webhook */
        $webhook = $this->doFind('id', $id);

        return $webhook;
    }

    public function generateWebhookId(): string
    {
        return $this->random->uuid();
    }

    public function store(WebhookInterface $webhook): WebhookInterface
    {
        $webhook->id($webhook->getId() ?? $this->generateWebhookId());

        try {
            // Attempt optimistic creation
            $this->doPutItem($webhook, 'id');
        } catch (ConditionalCheckFailedException) {
            // If we get here, the item already exists, so we update it
            $this->doPutItem(
                instance: $webhook,
                previousData: $this->doFindRaw('id', (string)$webhook->getId(), true)
            );
        }

        return $webhook;
    }

    /**
     * @param \EonX\EasyWebhook\Common\Entity\WebhookInterface $instance
     * @param \AsyncAws\DynamoDb\ValueObject\AttributeValue[]|null $previousData
     *
     * @return \AsyncAws\DynamoDb\ValueObject\AttributeValue[]
     *
     * @throws \Nette\Utils\JsonException
     */
    protected function createItemFromInstance(object $instance, ?array $previousData = null): array
    {
        $now = CarbonImmutable::now('UTC');
        $data = \array_merge($instance->toArray(), [
            'class' => $instance::class,
            'created_at' => isset($previousData['created_at']) ? $previousData['created_at']->getS() ?? $now : $now,
            'id' => $instance->getId(),
            'updated_at' => $now,
        ]);

        if (\is_array($instance->getExtra())) {
            $data['extra'] = $instance->getExtra();
        }

        return \array_map(
            static fn ($value): AttributeValue => AttributeValue::create(['S' => (string)$value]),
            $this->formatData($data)
        );
    }

    protected function getDefaultTable(): string
    {
        return self::DEFAULT_TABLE;
    }

    /**
     * @param \AsyncAws\DynamoDb\ValueObject\AttributeValue[] $item
     */
    protected function instantiateFromResultItem(array $item): object
    {
        $data = \array_map(
            static fn (AttributeValue $value): ?string => $value->getS(),
            $item
        );

        $class = $data['class'] ?? Webhook::class;
        $extra = [];

        // Quick fix, maybe we will need to think about something better if needed
        if (\is_string($data['http_options'] ?? null)) {
            $data['http_options'] = \json_decode($data['http_options'], true) ?? $data['http_options'];
        }

        if (\is_string($data['send_after'] ?? null)) {
            $data['send_after'] = CarbonImmutable::createFromFormat(self::DATETIME_FORMAT, $data['send_after']);
        }

        if (\is_string($data['extra'] ?? null)) {
            $extra = \json_decode($data['extra'], true) ?? $extra;
        }

        // Webhook from the store are already configured
        return $class::fromArray($data)
            ->extra($extra)
            ->configured(true);
    }
}
