<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Store;

use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;

final class NullResultStore implements ResultStoreInterface
{
    public function store(WebhookResultInterface $result): WebhookResultInterface
    {
        return $result;
    }
}
