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
        if ($this->isAllowedUrl($url)) {
            $options['max_redirects'] = 0;

            return new AsyncResponse($this->unprotectedClient, $method, $url, $options);
        }

        return $this->protectedClient->request($method, $url, $options);
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

    private function isAllowedUrl(string $url): bool
    {
        if ($this->allowedHosts === []) {
            return false;
        }

        $host = \parse_url($url, \PHP_URL_HOST);

        if (\is_string($host) === false || $host === '') {
            return false;
        }

        $host = \mb_strtolower(\trim($host, '[]'));

        if (\filter_var($host, \FILTER_VALIDATE_IP) !== false) {
            return false;
        }

        $port = \parse_url($url, \PHP_URL_PORT)
            ?? (\mb_strtolower((string)\parse_url($url, \PHP_URL_SCHEME)) === 'https' ? 443 : 80);

        return \in_array($host, $this->allowedHosts, true)
            || \in_array($host . ':' . $port, $this->allowedHosts, true);
    }
}
