<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks\Stores;

use EonX\EasyWebhooks\Interfaces\WebhookStoreInterface;

final class NullWebhookStore implements WebhookStoreInterface
{
    /**
     * @param mixed[] $data
     */
    public function store(array $data, ?string $id = null): void
    {
        // No body needed.
    }
}
