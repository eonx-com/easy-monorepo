<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Store;

use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;

final class ArrayResultStore extends AbstractStore implements ResultStoreInterface, ResetStoreInterface
{
    /**
     * @var \EonX\EasyWebhook\Common\Entity\WebhookResultInterface[]
     */
    private array $results = [];

    /**
     * @return \EonX\EasyWebhook\Common\Entity\WebhookResultInterface[]
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
            $result->setId($this->random->uuid());
        }

        return $this->results[$result->getId()] = $result;
    }
}
