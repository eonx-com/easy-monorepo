<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface MiddlewareInterface extends HasPriorityInterface
{
    /**
     * @var int
     */
    public const SEND_MIDDLEWARE_PRIORITY = 1000;

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface;
}
