<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Application\Common\HttpClient;

use DateTimeInterface;
use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use EonX\EasyHttpClient\Common\ValueObject\ResponseData;
use EonX\EasyHttpClient\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyHttpClient\Tests\Fixture\App\Client\SomeClient;
use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use EonX\EasyTest\HttpClient\Response\SimpleTestResponse;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class WithEventsHttpClientTest extends AbstractApplicationTestCase
{
    public function testRequestReturnsResponse(): void
    {
        TestResponseFactory::addResponse(new SimpleTestResponse('https://eonx.com/'));
        $sut = self::getContainer()->get(SomeClient::class);

        $sut->makeRequest();

        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());
        self::assertInstanceOf(HttpRequestSentEvent::class, $eventDispatcher->getDispatchedEvents()[0]);

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0];

        self::assertInstanceOf(ResponseData::class, $event->getResponseData());
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

        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());
        self::assertInstanceOf(HttpRequestSentEvent::class, $eventDispatcher->getDispatchedEvents()[0]);

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0];

        self::assertNull($event->getResponseData());
        self::assertSame($throwable, $event->getThrowable());
        self::assertInstanceOf(DateTimeInterface::class, $event->getThrowableThrownAt());
    }
}
