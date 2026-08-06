<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Factory;

use EonX\EasyWebhook\Common\HttpClient\RequestLimitsHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class HttpClientFactory implements HttpClientFactoryInterface
{
    public const DEFAULT_MAX_DURATION = 30;

    public const DEFAULT_MAX_RESPONSE_BYTES = 1024 * 1024;

    public const DEFAULT_TIMEOUT = 10;

    public function __construct(
        private bool $requestLimitsEnabled = false,
        private int $timeout = self::DEFAULT_TIMEOUT,
        private int $maxDuration = self::DEFAULT_MAX_DURATION,
        private int $maxResponseBytes = self::DEFAULT_MAX_RESPONSE_BYTES,
    ) {
    }

    public function create(): HttpClientInterface
    {
        $httpClient = HttpClient::create([
            'http_version' => '1.1',
        ]);

        // @todo Change the default to enabled (on) in 7.x
        // Opt-in: with the guards off, hand back the bare client
        if ($this->requestLimitsEnabled === false) {
            return $httpClient;
        }

        // Every limit is enforced inside the decorator, not as a client-default option: a webhook's
        // own http client options are merged per request and win over client defaults, so a
        // client-default timeout/max_duration could be silently overridden (or disabled with 0) by
        // the webhook. RequestLimitsHttpClient clamps them per request instead. Each limit is
        // no-op'd by the decorator when its value is 0
        return new RequestLimitsHttpClient(
            $httpClient,
            $this->timeout,
            $this->maxDuration,
            $this->maxResponseBytes,
        );
    }
}
