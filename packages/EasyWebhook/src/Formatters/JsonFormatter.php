<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Formatters;

use EonX\EasyWebhook\Interfaces\WebhookBodyFormatterInterface;
use Nette\Utils\Json;

final class JsonFormatter implements WebhookBodyFormatterInterface
{
    /**
     * @throws \Nette\Utils\JsonException
     */
    public function format(array $body): string
    {
        return Json::encode($body);
    }

    public function getContentTypeHeader(): string
    {
        return 'application/json';
    }
}
