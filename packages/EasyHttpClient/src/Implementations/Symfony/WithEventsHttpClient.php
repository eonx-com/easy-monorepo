<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Implementations\Symfony;

use Carbon\Carbon;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyHttpClient\Data\RequestData;
use EonX\EasyHttpClient\Data\ResponseData;
use EonX\EasyHttpClient\Events\HttpRequestSentEvent;
use EonX\EasyHttpClient\Interfaces\HttpOptionsInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

final class WithEventsHttpClient implements HttpClientInterface
{
    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $decorated;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher, HttpClientInterface $decorated)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->decorated = $decorated;
    }

    /**
     * @param null|mixed[] $options
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function request(string $method, string $url, ?array $options = null): ResponseInterface
    {
        $options = $options ?? [];
        $subscribers = $options[HttpOptionsInterface::REQUEST_DATA_SUBSCRIBERS] ?? [];
        unset($options[HttpOptionsInterface::REQUEST_DATA_SUBSCRIBERS]);

        $requestData = new RequestData($method, $options, Carbon::now('UTC'), $url);
        $response = $this->decorated->request($method, $url, $options);

        $responseData = new ResponseData(
            $response->getContent(false),
            $response->getHeaders(false),
            Carbon::now('UTC'),
            $response->getStatusCode()
        );

        $this->eventDispatcher->dispatch(new HttpRequestSentEvent($requestData, $responseData));

        foreach ($subscribers as $subscriber) {
            if (\is_callable($subscriber)) {
                $subscriber($requestData, $responseData);
            }
        }

        return $response;
    }

    /**
     * @param \Symfony\Contracts\HttpClient\ResponseInterface|iterable<\Symfony\Contracts\HttpClient\ResponseInterface> $responses
     */
    public function stream($responses, float $timeout = null): ResponseStreamInterface
    {
        return $this->decorated->stream($responses, $timeout);
    }
}
