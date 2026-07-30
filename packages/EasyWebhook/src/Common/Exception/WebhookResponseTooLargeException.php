<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Exception;

use Throwable;

/**
 * Thrown by RequestLimitsHttpClient when a webhook response body exceeds the configured size cap.
 * Symfony surfaces an exception thrown while streaming a response wrapped in its own
 * TransportException (with this one as the previous), so SendWebhookMiddleware still records it as
 * a request failure via that wrapper; the distinct type — located with isInChain(), which walks
 * that wrapped chain — lets the retry layer treat an oversized response as non-retryable, since
 * unlike a timeout, retrying would only re-download the same oversized body.
 */
final class WebhookResponseTooLargeException extends AbstractEasyWebhookException
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
