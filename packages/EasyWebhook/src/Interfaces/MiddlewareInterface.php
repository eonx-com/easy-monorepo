<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface MiddlewareInterface extends HasPriorityInterface
{
    public const PRIORITY_CORE_AFTER = 5000;

    public const PRIORITY_CORE_BEFORE = -5000;

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface;
}
