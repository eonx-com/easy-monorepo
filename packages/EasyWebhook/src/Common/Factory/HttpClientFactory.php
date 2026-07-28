<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Factory;

use EonX\EasyWebhook\Common\HttpClient\MaxResponseSizeHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class HttpClientFactory implements HttpClientFactoryInterface
{
    public function __construct(
        private int $timeout = 10,
        private int $maxDuration = 30,
        private int $maxResponseBytes = 10 * 1024 * 1024,
    ) {
    }

    public function create(): HttpClientInterface
    {
        $options = [
            'http_version' => '1.1',
        ];

        // Idle timeout: abort when the target stops sending data for this long. 0 keeps PHP's
        // default_socket_timeout
        if ($this->timeout > 0) {
            $options['timeout'] = $this->timeout;
        }

        // Total-duration cap: abort the request after this many seconds regardless of activity.
        // This is the guard against a slow/trickling target holding a worker open indefinitely
        // (Symfony's default is 0 = unlimited). 0 keeps it unlimited
        if ($this->maxDuration > 0) {
            $options['max_duration'] = $this->maxDuration;
        }

        $httpClient = HttpClient::create($options);

        // Cap the response body size so a huge/bomb response cannot exhaust memory (getContent)
        // or storage (persisted webhook result). 0 disables the cap
        if ($this->maxResponseBytes > 0) {
            $httpClient = new MaxResponseSizeHttpClient($httpClient, $this->maxResponseBytes);
        }

        return $httpClient;
    }
}
