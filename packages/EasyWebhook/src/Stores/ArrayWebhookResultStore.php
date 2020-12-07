<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyWebhook\Interfaces\ResettableWebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class ArrayWebhookResultStore extends AbstractIdAwareWebhookResultStore implements ResettableWebhookResultStoreInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultInterface[]
     */
    private $results = [];

    public function find(string $id): ?WebhookResultInterface
    {
        return $this->results[$id] ?? null;
    }

    /**
     * @return \EonX\EasyWebhook\Interfaces\WebhookResultInterface[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    public function reset(): void
    {
        $this->results = [];
    }

    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        if ($result->getWebhook()->getId() === null) {
            $result->getWebhook()
                ->id($this->generateWebhookId());
        }

        return $this->results[$result->getWebhook()->getId()] = $result;
    }
}
