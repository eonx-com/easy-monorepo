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
     * Whether a retry can change the result. Not for a rejected HTTP client option (the options of the webhook stay
     * the same) and not for an oversized response (it would only be downloaded again). Time-limit aborts are retryable.
     * The attempts left are not checked here
     */
    public static function isRetryable(WebhookResultInterface $result): bool
    {
        $throwable = $result->getThrowable();

        return ($throwable instanceof WebhookHttpClientOptionNotAllowedException) === false
            && WebhookResponseTooLargeException::isInChain($throwable) === false;
    }
}
