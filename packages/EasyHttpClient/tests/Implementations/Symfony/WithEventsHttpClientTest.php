<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Implementations\Symfony;

use DateTimeInterface;
use EonX\EasyHttpClient\Events\HttpRequestSentEvent;
use EonX\EasyHttpClient\Implementations\Symfony\WithEventsHttpClient;
use EonX\EasyHttpClient\Interfaces\ResponseDataInterface;
use EonX\EasyHttpClient\Tests\Stubs\EventDispatcherStub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Contracts\HttpClient\Test\TestHttpServer;
use Throwable;

final class WithEventsHttpClientTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        TestHttpServer::start();
    }

    public function testDispatchEventWhenRequestSuccessful(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $sut = new WithEventsHttpClient($eventDispatcher, new NativeHttpClient());

        $response = $sut->request('GET', 'http://localhost:8057/chunked');

        $this->assertSame('Symfony is awesome!', $response->getContent());
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());
        self::assertInstanceOf(HttpRequestSentEvent::class, $eventDispatcher->getDispatchedEvents()[0]);
        /** @var \EonX\EasyHttpClient\Events\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0];
        self::assertInstanceOf(ResponseDataInterface::class, $event->getResponseData());
        $this->assertSame('Symfony is awesome!', $event->getResponseData()->getContent());
        self::assertNull($event->getThrowable());
        self::assertNull($event->getThrowableThrownAt());
    }

    public function testDispatchEventWhenRequestThrowsException(): void
    {
        $eventDispatcher = new EventDispatcherStub();
        $sut = new WithEventsHttpClient($eventDispatcher, new NativeHttpClient());

        try {
            $response = $sut->request('GET', 'http://localhost:8057/timeout-header', ['timeout' => 0.1]);
            $response->getContent();
        } catch (Throwable $throwable) {
            self::assertInstanceOf(TransportException::class, $throwable);
        }

        self::assertCount(1, $eventDispatcher->getDispatchedEvents());
        self::assertInstanceOf(HttpRequestSentEvent::class, $eventDispatcher->getDispatchedEvents()[0]);
        /** @var \EonX\EasyHttpClient\Events\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0];
        self::assertNull($event->getResponseData());
        self::assertInstanceOf(TransportException::class, $event->getThrowable());
        self::assertInstanceOf(DateTimeInterface::class, $event->getThrowableThrownAt());
    }
}
