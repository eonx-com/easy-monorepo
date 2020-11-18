<?php

declare(strict_types=1);

namespace EonX\EasyCore\HttpClient\Symfony;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

final class WithLoggingHttpClient implements HttpClientInterface
{
    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $decorated;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(HttpClientInterface $decorated, LoggerInterface $logger)
    {
        $this->decorated = $decorated;
        $this->logger = $logger;
    }

    public function request(string $method, string $url, ?array $options = null): ResponseInterface
    {
        $options = $options ?? [];

        $this->logger->debug(\sprintf('Request: "%s %s"', $method, $url), ['http_options' => $options]);

        $response = $this->decorated->request($method, $url, $options);

        $this->logger->debug(\sprintf('Response: "%d %s"', $response->getStatusCode(), $url), [
            'content' => $response->getContent(false),
            'info' => $response->getInfo(),
        ]);

        return $response;
    }

    /**
     * @param ResponseInterface|ResponseInterface[] $responses One or more responses created by the current HTTP client
     */
    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        // Logging not supported for stream (at least not yet)
        return $this->decorated->stream($responses, $timeout);
    }
}
