<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Implementations\Symfony;

use DateTimeInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyHttpClient\Events\HttpRequestSentEvent;
use EonX\EasyHttpClient\Interfaces\ResponseDataInterface;
use EonX\EasyHttpClient\Tests\AbstractSymfonyTestCase;
use EonX\EasyHttpClient\Tests\Bridge\Symfony\Fixtures\App\Client\SomeClient;
use EonX\EasyTest\HttpClient\SimpleTestResponse;
use EonX\EasyTest\HttpClient\TestResponseFactory;
use Symfony\Component\HttpClient\Exception\TransportException;
use Throwable;

final class WithEventsHttpClientTest extends AbstractSymfonyTestCase
{
    public function testRequestReturnsResponse(): void
    {
        TestResponseFactory::addResponse(new SimpleTestResponse('https://eonx.com/'));
        $sut = self::getContainer()->get(SomeClient::class);

        $sut->makeRequest();

        /** @var \EonX\EasyHttpClient\Tests\Stubs\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
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
        TestResponseFactory::addResponse(new SimpleTestResponse(
            url: 'https://eonx.com/',
            responseData: new TransportException(),
        ));
        $sut = self::getContainer()->get(SomeClient::class);
        $throwable = null;

        try {
            $sut->makeRequest();
        } catch (Throwable $throwable) {
            self::assertInstanceOf(TransportException::class, $throwable);
        }

        /** @var \EonX\EasyHttpClient\Tests\Stubs\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());
        self::assertInstanceOf(HttpRequestSentEvent::class, $eventDispatcher->getDispatchedEvents()[0]);

        /** @var \EonX\EasyHttpClient\Events\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0];

        self::assertNull($event->getResponseData());
        self::assertSame($throwable, $event->getThrowable());
        self::assertInstanceOf(DateTimeInterface::class, $event->getThrowableThrownAt());
    }
}
