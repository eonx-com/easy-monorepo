<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\HttpClient;

use EonX\EasyWebhook\Common\Exception\WebhookResponseTooLargeException;
use Generator;
use Symfony\Component\HttpClient\AsyncDecoratorTrait;
use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Enforces the configured DoS request limits (idle timeout, total duration, response body size) so
 * a huge/slow/bomb webhook response cannot exhaust a worker, memory, or storage.
 *
 * The time limits are clamped onto the per-request options rather than set as client defaults:
 * SendWebhookMiddleware passes the webhook's own http client options per request, and per-request
 * options win over client defaults, so a webhook carrying `timeout`/`max_duration: 0` could
 * otherwise silently disable those two guards. Clamping here means a per-webhook value can only
 * make a limit stricter, never raise or remove the configured ceiling.
 *
 * The size limit counts the DECODED chunks the transport yields, so a small gzip payload that
 * inflates past the cap is rejected too, unlike a check on the wire (Content-Length / transfer)
 * size. It cannot be overridden per request at all.
 */
final class RequestLimitsHttpClient implements HttpClientInterface
{
    use AsyncDecoratorTrait;

    public function __construct(
        HttpClientInterface $client,
        private readonly int $timeout,
        private readonly int $maxDuration,
        private readonly int $maxResponseBytes,
    ) {
        $this->client = $client;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $options = $this->clampTimeLimit($options, 'timeout', $this->timeout);
        $options = $this->clampTimeLimit($options, 'max_duration', $this->maxDuration);

        $maxResponseBytes = $this->maxResponseBytes;
        $decodedBytes = 0;

        return new AsyncResponse(
            $this->client,
            $method,
            $url,
            $options,
            static function (
                ChunkInterface $chunk,
                AsyncContext $context,
            ) use ($maxResponseBytes, &$decodedBytes): Generator {
                if ($maxResponseBytes <= 0) {
                    yield $chunk;

                    return;
                }

                // A timeout chunk carries no content; let it pass so the transport can surface the
                // timeout or resume. isTimeout() throws on a network error, which propagates as a
                // genuine request failure
                if ($chunk->isTimeout()) {
                    yield $chunk;

                    return;
                }

                // Headers just arrived: reject a declared oversized body up front. Content-Length
                // is the wire size, so this only pre-empts a plain (uncompressed) oversized body;
                // a compressed body slips past here and is caught below on its decoded size
                if ($chunk->isFirst()) {
                    $contentLength = (int)($context->getHeaders()['content-length'][0] ?? '0');

                    if ($contentLength > $maxResponseBytes) {
                        throw new WebhookResponseTooLargeException(\sprintf(
                            'Webhook response exceeded the maximum allowed size of %d bytes',
                            $maxResponseBytes
                        ));
                    }

                    yield $chunk;

                    return;
                }

                // Data/last chunks: getContent() is the decoded body (empty for the last chunk), so
                // this counts what actually lands in memory. Throwing aborts the transfer, so the
                // body is never fully buffered
                $decodedBytes += \strlen($chunk->getContent());

                if ($decodedBytes > $maxResponseBytes) {
                    throw new WebhookResponseTooLargeException(\sprintf(
                        'Webhook response exceeded the maximum allowed size of %d bytes',
                        $maxResponseBytes
                    ));
                }

                yield $chunk;
            }
        );
    }

    /**
     * Forces the per-request value for a time limit to be no weaker than the configured ceiling.
     * A ceiling of 0 means the limit is disabled by config, so the request value is left untouched.
     */
    private function clampTimeLimit(array $options, string $key, int $ceiling): array
    {
        if ($ceiling <= 0) {
            return $options;
        }

        $requested = $options[$key] ?? null;

        // A positive per-request value may only shorten the limit; 0/absent/non-numeric enforces
        // the ceiling (0 would otherwise mean "unlimited"/"PHP default", disabling the guard)
        $options[$key] = \is_numeric($requested) && $requested > 0
            ? \min($requested + 0, $ceiling)
            : $ceiling;

        return $options;
    }
}
