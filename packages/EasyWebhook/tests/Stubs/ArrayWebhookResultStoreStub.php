<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyWebhook\Interfaces\IdAwareWebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class ArrayWebhookResultStoreStub implements IdAwareWebhookResultStoreInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultInterface[]
     */
    private $results = [];

    /**
     * @var null|string
     */
    private $webhookId;

    public function __construct(?string $webhookId = null)
    {
        $this->webhookId = $webhookId;
    }

    public function find(string $id): ?WebhookResultInterface
    {
        return null;
    }

    public function generateWebhookId(): string
    {
        return $this->webhookId ?? 'webhook-id';
    }

    /**
     * @return \EonX\EasyWebhook\Interfaces\WebhookResultInterface[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        if ($this->webhookId !== null) {
            $result->getWebhook()
                ->id($this->webhookId);
        }

        $this->results[] = $result;

        return $result;
    }
}
