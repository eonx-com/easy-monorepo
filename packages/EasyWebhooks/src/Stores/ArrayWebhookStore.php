<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks\Stores;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhooks\Interfaces\WebhookStoreInterface;

final class ArrayWebhookStore implements WebhookStoreInterface
{
    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    /**
     * @var mixed[]
     */
    private $webhooks = [];

    public function __construct(RandomGeneratorInterface $random)
    {
        $this->random = $random;
    }

    public function getWebhooks(): array
    {
        return $this->webhooks;
    }

    public function reset(): void
    {
        $this->webhooks = [];
    }

    /**
     * @param mixed[] $data
     */
    public function store(array $data, ?string $id = null): void
    {
        $this->webhooks[$id ?? $this->random->uuidV4()] = $data;
    }
}
