<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyWebhook\Interfaces\IdAwareWebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class ArrayWebhookResultStoreStub implements IdAwareWebhookResultStoreInterface
{
    /**
     * @var string
     */
    private $webhookId;

    public function __construct(string $webhookId)
    {
        $this->webhookId = $webhookId;
    }

    public function find(string $id): ?WebhookResultInterface
    {
        return null;
    }

    public function generateWebhookId(): string
    {
        return $this->webhookId;
    }

    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        return $result;
    }
}
