<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Dispatcher;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;

interface AsyncDispatcherInterface
{
    public function dispatch(WebhookInterface $webhook): void;
}
