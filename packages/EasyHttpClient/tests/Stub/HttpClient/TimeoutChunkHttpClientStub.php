<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Stub\HttpClient;

use Generator;
use Symfony\Component\HttpClient\Chunk\ErrorChunk;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

/**
 * After the headers, the first poll with a zero timeout yields only a timeout chunk, like a real transport does while
 * the body is not received yet.
 */
final class TimeoutChunkHttpClientStub implements HttpClientInterface
{
    private readonly HttpClientInterface $decorated;

    private bool $isHeadersReceived = false;

    private bool $isTimeoutYielded = false;

    public function __construct(MockResponse $response)
    {
        $this->decorated = new MockHttpClient($response);
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->decorated->request($method, $url, $options);
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        if ($responses instanceof ResponseInterface) {
            $responses = [$responses];
        }

        if ($timeout === 0.0 && $this->isHeadersReceived && $this->isTimeoutYielded === false) {
            $this->isTimeoutYielded = true;

            return new ResponseStream($this->yieldTimeoutChunks($responses));
        }

        return new ResponseStream($this->yieldChunks($responses, $timeout));
    }

    public function withOptions(array $options): static
    {
        return $this;
    }

    private function yieldChunks(iterable $responses, ?float $timeout): Generator
    {
        foreach ($this->decorated->stream($responses, $timeout) as $response => $chunk) {
            if ($chunk->isFirst()) {
                $this->isHeadersReceived = true;
            }

            yield $response => $chunk;
        }
    }

    private function yieldTimeoutChunks(iterable $responses): Generator
    {
        foreach ($responses as $response) {
            /** @var string $url */
            $url = $response->getInfo('url');

            yield $response => new ErrorChunk(0, \sprintf('Idle timeout reached for "%s".', $url));
        }
    }
}
