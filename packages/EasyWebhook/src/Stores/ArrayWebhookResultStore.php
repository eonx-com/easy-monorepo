<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;

final class ArrayWebhookResultStore implements WebhookResultStoreInterface
{
    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultInterface[]
     */
    private $results = [];

    public function __construct(RandomGeneratorInterface $random)
    {
        $this->random = $random;
    }

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
            $result->getWebhook()->setId($this->random->uuidV4());
        }

        return $this->results[$result->getWebhook()->getId()] = $result;
    }
}
