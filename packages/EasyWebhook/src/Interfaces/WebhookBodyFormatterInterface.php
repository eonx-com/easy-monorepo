<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

interface WebhookBodyFormatterInterface
{
    public function format(array $body): string;

    public function getContentTypeHeader(): string;
}
