<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\HttpClient;

use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\Service\ResetInterface;

final class AllowedHostsHttpClient implements HttpClientInterface, ResetInterface
{
    public function __construct(
        private HttpClientInterface $protectedClient,
        private HttpClientInterface $unprotectedClient,
        private readonly array $allowedHosts,
    ) {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $host = self::parseHost($url);

        if ($host !== null && $this->isAllowedHost($host)) {
            $options['max_redirects'] ??= 0;

            return new AsyncResponse($this->unprotectedClient, $method, $url, $options);
        }

        return new AsyncResponse($this->protectedClient, $method, $url, $options);
    }

    public function reset(): void
    {
        if ($this->protectedClient instanceof ResetInterface) {
            $this->protectedClient->reset();
        }

        if ($this->unprotectedClient instanceof ResetInterface) {
            $this->unprotectedClient->reset();
        }
    }

    public function stream(ResponseInterface|iterable $responses, ?float $timeout = null): ResponseStreamInterface
    {
        if ($responses instanceof ResponseInterface) {
            $responses = [$responses];
        }

        return new ResponseStream(AsyncResponse::stream($responses, $timeout, self::class));
    }

    public function withOptions(array $options): static
    {
        $clone = clone $this;
        $clone->protectedClient = $this->protectedClient->withOptions($options);
        $clone->unprotectedClient = $this->unprotectedClient->withOptions($options);

        return $clone;
    }

    private static function parseHost(string $url): ?string
    {
        $host = \parse_url($url, \PHP_URL_HOST);

        if (\is_string($host) === false || $host === '') {
            return null;
        }

        return \mb_strtolower(\trim($host, '[]'));
    }

    private function isAllowedHost(string $host): bool
    {
        if ($this->allowedHosts === []) {
            return false;
        }

        if (\filter_var($host, \FILTER_VALIDATE_IP) !== false) {
            return false;
        }

        return \in_array($host, $this->allowedHosts, true);
    }
}
