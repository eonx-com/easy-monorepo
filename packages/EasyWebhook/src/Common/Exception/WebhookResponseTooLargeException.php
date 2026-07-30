<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Exception;

use Symfony\Component\HttpClient\Exception\TransportException;
use Throwable;

/**
 * Thrown when a webhook response exceeds the configured size cap. It extends the HttpClient
 * TransportException so SendWebhookMiddleware still treats it as a request failure, but being a
 * distinct type lets the retry layer treat an oversized response as non-retryable: unlike a
 * timeout, retrying would only re-download the same oversized body.
 */
final class WebhookResponseTooLargeException extends TransportException
{
    public static function isInChain(?Throwable $throwable): bool
    {
        while ($throwable !== null) {
            if ($throwable instanceof self) {
                return true;
            }

            $throwable = $throwable->getPrevious();
        }

        return false;
    }
}
