<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\HttpClient;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\Service\ResetInterface;

final class AllowedHostsHttpClient implements HttpClientInterface, LoggerAwareInterface, ResetInterface
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

        if ($host !== null) {
            $options = self::pinResolvedIp($host, $url, $options);
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

    public function setLogger(LoggerInterface $logger): void
    {
        if ($this->protectedClient instanceof LoggerAwareInterface) {
            $this->protectedClient->setLogger($logger);
        }

        if ($this->unprotectedClient instanceof LoggerAwareInterface) {
            $this->unprotectedClient->setLogger($logger);
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

    private static function pinResolvedIp(string $host, string $url, array $options): array
    {
        if (\filter_var($host, \FILTER_VALIDATE_IP) !== false) {
            return $options;
        }

        foreach (\array_keys(\is_array($options['resolve'] ?? null) ? $options['resolve'] : []) as $pinnedHost) {
            if (\mb_strtolower((string)$pinnedHost) === $host) {
                return $options;
            }
        }

        $ipv4 = \gethostbynamel($host);
        $ip = \is_array($ipv4) ? ($ipv4[0] ?? null) : null;

        if ($ip === null) {
            $ipv6 = \dns_get_record($host, \DNS_AAAA);
            $ip = \is_array($ipv6) ? ($ipv6[0]['ipv6'] ?? null) : null;
        }

        if (\is_string($ip) === false) {
            throw new TransportException(\sprintf('Host "%s" could not be resolved for "%s".', $host, $url));
        }

        $options['resolve'][$host] = $ip;

        return $options;
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
