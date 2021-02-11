<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyWebhook\Interfaces\Stores\ResultStoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class NullResultStore implements ResultStoreInterface
{
    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        return $result;
    }
}
