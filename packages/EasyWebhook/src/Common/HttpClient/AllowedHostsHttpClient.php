<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\HttpClient;

use Symfony\Component\HttpClient\AsyncDecoratorTrait;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Service\ResetInterface;

final class AllowedHostsHttpClient implements HttpClientInterface, ResetInterface
{
    use AsyncDecoratorTrait;

    public function __construct(
        private HttpClientInterface $protectedClient,
        HttpClientInterface $unprotectedClient,
        private readonly array $allowedHosts,
    ) {
        $this->client = $unprotectedClient;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if ($this->isAllowedUrl($url)) {
            $options['max_redirects'] = 0;

            return new AsyncResponse($this->client, $method, $url, $options);
        }

        return $this->protectedClient->request($method, $url, $options);
    }

    public function reset(): void
    {
        if ($this->protectedClient instanceof ResetInterface) {
            $this->protectedClient->reset();
        }

        if ($this->client instanceof ResetInterface) {
            $this->client->reset();
        }
    }

    public function withOptions(array $options): static
    {
        $clone = clone $this;
        $clone->protectedClient = $this->protectedClient->withOptions($options);
        $clone->client = $this->client->withOptions($options);

        return $clone;
    }

    private function isAllowedUrl(string $url): bool
    {
        if ($this->allowedHosts === []) {
            return false;
        }

        $scheme = \mb_strtolower((string)\parse_url($url, \PHP_URL_SCHEME));

        if (\in_array($scheme, ['http', 'https'], true) === false) {
            return false;
        }

        $host = \parse_url($url, \PHP_URL_HOST);

        if (\is_string($host) === false || $host === '') {
            return false;
        }

        $host = \mb_strtolower(\trim($host, '[]'));

        if ($this->isIpLiteral($host)) {
            return false;
        }

        $defaultPort = $scheme === 'https' ? 443 : 80;
        $port = \parse_url($url, \PHP_URL_PORT) ?? $defaultPort;

        return (\in_array($host, $this->allowedHosts, true) && $port === $defaultPort)
            || \in_array($host . ':' . $port, $this->allowedHosts, true);
    }

    private function isIpLiteral(string $host): bool
    {
        $host = \rtrim($host, '.');

        if (\filter_var($host, \FILTER_VALIDATE_IP) !== false) {
            return true;
        }

        $lastLabel = \mb_strtolower((string)\strrchr('.' . $host, '.'));

        return \preg_match('/^\.(0x[0-9a-f]+|[0-9]+)$/', $lastLabel) === 1;
    }
}
