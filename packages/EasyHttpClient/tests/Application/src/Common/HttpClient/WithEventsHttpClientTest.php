<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Application\Common\HttpClient;

use DateTimeInterface;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface as EasyEventDispatcherInterface;
use EonX\EasyHttpClient\Common\Enum\HttpOption;
use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use EonX\EasyHttpClient\Common\HttpClient\WithEventsHttpClient;
use EonX\EasyHttpClient\Common\ValueObject\ResponseData;
use EonX\EasyHttpClient\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyHttpClient\Tests\Fixture\App\Client\SomeClient;
use EonX\EasyHttpClient\Tests\Stub\HttpClient\TimeoutChunkHttpClientStub;
use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use EonX\EasyTest\HttpClient\Response\SimpleTestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class WithEventsHttpClientTest extends AbstractApplicationTestCase
{
    /**
     * @see testResponseWithErrorStatusCodeStillDispatchesEvent
     */
    public static function provideErrorStatusCodes(): iterable
    {
        yield 'client error 404' => [404];

        yield 'server error 502' => [502];
    }

    public function testCancelledResponseDoesNotDispatchAnyEvent(): void
    {
        TestResponseFactory::addResponse(new SimpleTestResponse('https://eonx.com/'));
        $sut = self::getContainer()->get(SomeClient::class);

        $sut->makeRequest()
            ->cancel();

        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(0, $eventDispatcher->getDispatchedEvents());
    }

    public function testIdleTimeoutDispatchesEventWithThrowable(): void
    {
        $sut = new WithEventsHttpClient(
            self::getContainer()->get(EasyEventDispatcherInterface::class),
            new MockHttpClient(new MockResponse(['', 'some-content']))
        );
        $response = $sut->request('GET', 'https://eonx.com/');

        try {
            $response->getContent();

            self::fail('The idle timeout is not thrown.');
        } catch (TransportExceptionInterface) {
            // The consumer gives up and the response is destructed
            unset($response);
        }

        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0];

        self::assertNull($event->getResponseData());
        self::assertInstanceOf(TimeoutException::class, $event->getThrowable());
        self::assertSame('Idle timeout reached for "https://eonx.com/".', $event->getThrowable()->getMessage());
    }

    public function testPsr18ClientReadsBodyAfterTimeoutChunks(): void
    {
        $sut = new Psr18Client(new WithEventsHttpClient(
            self::getContainer()->get(EasyEventDispatcherInterface::class),
            new TimeoutChunkHttpClientStub(new MockResponse('some-content'))
        ));

        $response = $sut->sendRequest($sut->createRequest('GET', 'https://eonx.com/'));

        self::assertSame('some-content', (string)$response->getBody());
        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0];

        self::assertInstanceOf(ResponseData::class, $event->getResponseData());
        self::assertSame('some-content', $event->getResponseData()->getContent());
        self::assertNull($event->getThrowable());
    }

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

    public function testRequestWithEventsDisabledDoesNotDispatchAnyEvent(): void
    {
        TestResponseFactory::addResponse(new SimpleTestResponse('https://eonx.com/'));
        $sut = self::getContainer()->get(SomeClient::class);

        $sut->makeRequest([HttpOption::EventsEnabled->value => false])
            ->getContent();

        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(0, $eventDispatcher->getDispatchedEvents());
    }

    #[DataProvider('provideErrorStatusCodes')]
    public function testResponseWithErrorStatusCodeStillDispatchesEvent(int $statusCode): void
    {
        TestResponseFactory::addResponse(new SimpleTestResponse(url: 'https://eonx.com/', responseCode: $statusCode));
        $sut = self::getContainer()->get(SomeClient::class);

        $sut->makeRequest()
            ->getContent(false);

        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0];

        self::assertInstanceOf(ResponseData::class, $event->getResponseData());
        self::assertSame($statusCode, $event->getResponseData()->getStatusCode());
        self::assertNull($event->getThrowable());
    }

    public function testScopingHttpClientWithEventsDisabledDoesNotDispatchAnyEvent(): void
    {
        TestResponseFactory::addResponse(new SimpleTestResponse('https://eonx.com/'));
        $httpClient = self::getContainer()->get(HttpClientInterface::class);
        $sut = ScopingHttpClient::forBaseUri($httpClient, 'https://eonx.com/', [
            HttpOption::EventsEnabled->value => false,
        ]);

        $sut->request('GET', 'https://eonx.com/')
            ->getContent();

        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(0, $eventDispatcher->getDispatchedEvents());
    }
}
