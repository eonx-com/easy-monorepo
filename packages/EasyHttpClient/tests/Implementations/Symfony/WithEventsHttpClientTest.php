<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Implementations\Symfony;

use EonX\EasyHttpClient\Events\HttpRequestSentEvent;
use EonX\EasyHttpClient\Implementations\Symfony\WithEventsHttpClient;
use EonX\EasyHttpClient\Interfaces\ResponseDataInterface;
use EonX\EasyHttpClient\Tests\AbstractTestCase;
use EonX\EasyHttpClient\Tests\Stubs\EventDispatcherStub;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class WithEventsHttpClientTest extends AbstractTestCase
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Throwable
     */
    public function testRequestReturnsResponse(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $httpClient = new MockHttpClient(new MockResponse(''));
        $withEventsHttpClient = new WithEventsHttpClient($eventDispatcher, $httpClient);

        $response = $withEventsHttpClient->request('POST', 'https://eonx.com');

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());
        self::assertInstanceOf(HttpRequestSentEvent::class, $eventDispatcher->getDispatchedEvents()[0]);

        /** @var \EonX\EasyHttpClient\Events\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0];

        self::assertInstanceOf(ResponseDataInterface::class, $event->getResponseData());
        self::assertNull($event->getThrowable());
        self::assertNull($event->getThrowableThrownAt());
    }

    public function testRequestThrowsException(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $httpClient = new MockHttpClient(function (): string {
            return 'invalid';
        });
        $withEventsHttpClient = new WithEventsHttpClient($eventDispatcher, $httpClient);
        $throwable = null;

        try {
            $withEventsHttpClient->request('POST', 'https://eonx.com');
        } catch (\Throwable $throwable) {
            self::assertInstanceOf(TransportException::class, $throwable);
        }

        self::assertCount(1, $eventDispatcher->getDispatchedEvents());
        self::assertInstanceOf(HttpRequestSentEvent::class, $eventDispatcher->getDispatchedEvents()[0]);

        /** @var \EonX\EasyHttpClient\Events\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0];

        self::assertNull($event->getResponseData());
        self::assertSame($throwable, $event->getThrowable());
        self::assertInstanceOf(\DateTimeInterface::class, $event->getThrowableThrownAt());
    }
}
