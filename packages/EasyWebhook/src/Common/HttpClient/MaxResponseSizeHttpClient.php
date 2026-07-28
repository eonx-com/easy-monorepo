<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\HttpClient;

use Symfony\Component\HttpClient\DecoratorTrait;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Aborts a response whose body exceeds a byte limit, before the full body is ever buffered into
 * memory (or persisted by a result store). This guards against a malicious/huge webhook response
 * causing memory or storage exhaustion.
 */
final class MaxResponseSizeHttpClient implements HttpClientInterface
{
    use DecoratorTrait;

    public function __construct(
        HttpClientInterface $client,
        private readonly int $maxResponseBytes,
    ) {
        $this->client = $client;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $maxResponseBytes = $this->maxResponseBytes;
        $innerOnProgress = $options['on_progress'] ?? null;

        // $dlNow is the number of body bytes received so far, so this catches chunked responses
        // that advertise no Content-Length; $dlSize is the advertised total, letting us reject a
        // large Content-Length up front. Throwing aborts the transfer mid-download, so the full
        // body is never buffered. Any caller-supplied on_progress is preserved and still runs
        $options['on_progress'] = static function (
            int $dlNow,
            int $dlSize,
            array $info,
        ) use ($maxResponseBytes, $innerOnProgress): void {
            if ($dlNow > $maxResponseBytes || $dlSize > $maxResponseBytes) {
                throw new TransportException(\sprintf(
                    'Webhook response exceeded the maximum allowed size of %d bytes',
                    $maxResponseBytes
                ));
            }

            if ($innerOnProgress !== null) {
                $innerOnProgress($dlNow, $dlSize, $info);
            }
        };

        return $this->client->request($method, $url, $options);
    }
}
