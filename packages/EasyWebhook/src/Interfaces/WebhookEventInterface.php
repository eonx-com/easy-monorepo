<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface WebhookEventInterface
{
    public function getResult(): WebhookResultInterface;
}
