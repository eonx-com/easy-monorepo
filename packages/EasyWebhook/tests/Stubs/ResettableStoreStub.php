<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyWebhook\Interfaces\ResettableWebhookResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class ResettableStoreStub implements ResettableWebhookResultStoreInterface
{
    private $calls = 0;

    public function find(string $id): ?WebhookResultInterface
    {
        return null;
    }

    public function getCalls(): int
    {
        return $this->calls;
    }

    public function reset(): void
    {
        ++$this->calls;
    }

    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        return $result;
    }
}
