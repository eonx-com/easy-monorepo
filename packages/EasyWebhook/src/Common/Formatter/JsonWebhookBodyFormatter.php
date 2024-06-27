<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Formatter;

use Nette\Utils\Json;

final class JsonWebhookBodyFormatter implements WebhookBodyFormatterInterface
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
