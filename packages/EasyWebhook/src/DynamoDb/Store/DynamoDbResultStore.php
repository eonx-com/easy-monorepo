<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\DynamoDb\Store;

use AsyncAws\DynamoDb\Exception\ConditionalCheckFailedException;
use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use Carbon\CarbonImmutable;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\MethodNotImplementedException;
use EonX\EasyWebhook\Common\Helper\WebhookResultHelper;
use EonX\EasyWebhook\Common\Store\ResultStoreInterface;

final class DynamoDbResultStore extends AbstractDynamoDbStore implements ResultStoreInterface
{
    public const string DEFAULT_TABLE = 'easy_webhook_results';

    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        $result->setId($result->getId() ?? $this->random->uuid());

        try {
            // Attempt optimistic creation
            $this->doPutItem($result, 'id');
        } catch (ConditionalCheckFailedException) {
            // If we get here, the item already exists, so we update it
            $this->doPutItem(
                instance: $result,
                previousData: $this->doFindRaw('id', (string)$result->getId(), true)
            );
        }

        return $result;
    }

    /**
     * @param \EonX\EasyWebhook\Common\Entity\WebhookResultInterface $instance
     *
     * @return \AsyncAws\DynamoDb\ValueObject\AttributeValue[]
     *
     * @throws \Nette\Utils\JsonException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function createItemFromInstance(object $instance, ?array $previousData = null): array
    {
        $now = CarbonImmutable::now('UTC');
        $data = \array_merge(WebhookResultHelper::getResultData($instance), [
            'created_at' => isset($previousData['created_at']) ? $previousData['created_at']->getS() ?? $now : $now,
            'updated_at' => $now,
        ]);

        return \array_map(
            static fn ($value): AttributeValue => AttributeValue::create(['S' => (string)$value]),
            $this->formatData($data)
        );
    }

    protected function getDefaultTable(): string
    {
        return self::DEFAULT_TABLE;
    }

    protected function instantiateFromResultItem(array $item): object
    {
        throw new MethodNotImplementedException('This store does not support fetching WebhookResult instances');
    }
}
