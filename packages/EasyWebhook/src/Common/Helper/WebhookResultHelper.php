<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Helper;

use EonX\EasyUtils\Common\Helper\ErrorDetailsHelper;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\WebhookHttpClientOptionNotAllowedException;
use EonX\EasyWebhook\Common\Exception\WebhookResponseTooLargeException;

final class WebhookResultHelper
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public static function getResultData(WebhookResultInterface $result): array
    {
        $webhook = $result->getWebhook();
        $response = $result->getResponse();
        $throwable = $result->getThrowable();

        $data = [
            'http_options' => $webhook->getHttpClientOptions(),
            'method' => $webhook->getMethod(),
            'url' => $webhook->getUrl(),
            'webhook_class' => $webhook::class,
            'webhook_id' => $webhook->getId(),
        ];

        if ($response !== null) {
            $data['response'] = [
                'content' => $response->getContent(false),
                'headers' => $response->getHeaders(false),
                'info' => $response->getInfo(),
                'status_code' => $response->getStatusCode(),
            ];
        }

        if ($throwable !== null) {
            $data['throwable'] = ErrorDetailsHelper::resolveSimpleDetails($throwable);
        }

        return $data;
    }

    /**
     * A failure that a retry cannot change, whatever the attempts left: the options of the webhook stay the same,
     * and an oversized response would only be downloaded again. Time-limit aborts remain retryable
     */
    public static function isNotRetryable(WebhookResultInterface $result): bool
    {
        $throwable = $result->getThrowable();

        return $throwable instanceof WebhookHttpClientOptionNotAllowedException
            || WebhookResponseTooLargeException::isInChain($throwable);
    }
}
