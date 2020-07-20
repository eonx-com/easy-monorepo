<?php
declare(strict_types=1);

namespace EonX\EasyWebhooks\Interfaces;

interface WebhookBodyFormatterInterface
{
    public function format(array $body): string;

    public function getContentTypeHeader(): string;
}
