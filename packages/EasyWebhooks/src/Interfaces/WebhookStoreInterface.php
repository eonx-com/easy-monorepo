<?php

declare(strict_types=1);

namespace EonX\EasyWebhooks\Interfaces;

interface WebhookStoreInterface
{
    /**
     * @var string
     */
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function store(array $data, ?string $id = null): void;
}
