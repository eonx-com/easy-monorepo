<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyWebhook\Interfaces\Stores\ResetStoreInterface;
use EonX\EasyWebhook\Interfaces\Stores\ResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class ArrayResultStore extends AbstractStore implements ResultStoreInterface, ResetStoreInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultInterface[]
     */
    private $results = [];

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
        if ($result->getId() === null) {
            $result->setId($this->random->uuidV4());
        }

        return $this->results[$result->getId()] = $result;
    }
}
