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
use RuntimeException;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\Response\StreamWrapper;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class WithEventsHttpClientTest extends AbstractApplicationTestCase
{
    /**
     * @see testBufferedResponseDispatchesEventWithContent
     */
    public static function provideBufferedRequestOptions(): iterable
    {
        yield 'buffer option is not set' => [[]];

        yield 'buffer option is true' => [[
            'buffer' => true,
        ]];
    }

    /**
     * @see testResponseWithErrorStatusCodeStillDispatchesEvent
     */
    public static function provideErrorStatusCodes(): iterable
    {
        yield 'client error 404' => [404];

        yield 'server error 502' => [502];
    }

    /**
     * @see testResponseWithOwnBufferOptionDispatchesEventWithoutContent
     */
    public static function provideOwnBufferOptions(): iterable
    {
        yield 'not buffered' => [false];

        yield 'stream resource' => [\fopen('php://temp', 'w+')];

        yield 'closure that returns false' => [static fn(): bool => false];

        yield 'closure that returns true' => [static fn(): bool => true];

        yield 'closure that returns a stream resource' => [static fn(): mixed => \fopen('php://temp', 'w+')];
    }

    #[DataProvider('provideBufferedRequestOptions')]
    public function testBufferedResponseDispatchesEventWithContent(array $options): void
    {
        TestResponseFactory::addResponse(new SimpleTestResponse('https://eonx.com/', responseData: 'some-content'));
        $sut = self::getContainer()->get(HttpClientInterface::class);

        $response = $sut->request('GET', 'https://eonx.com/', $options);

        self::assertSame('some-content', $response->getContent());
        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());
        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0]['event'];
        self::assertSame('some-content', $event->getResponseData()?->getContent());
    }

    public function testCancelledResponseAfterIdleTimeoutDispatchesEventWithThrowable(): void
    {
        $sut = new WithEventsHttpClient(
            self::getContainer()->get(EasyEventDispatcherInterface::class),
            new MockHttpClient(new MockResponse(['', 'some-content']))
        );
        $response = $sut->request('GET', 'https://eonx.com/');
        $throwable = null;

        try {
            $response->getContent();
        } catch (TransportExceptionInterface $throwable) {
        }
        $response->cancel();

        self::assertInstanceOf(TimeoutException::class, $throwable);
        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0]['event'];

        self::assertNull($event->getResponseData());
        self::assertInstanceOf(TimeoutException::class, $event->getThrowable());
    }

    public function testCancelledResponseAfterTimeoutChunkAndTransportErrorDispatchesOneEvent(): void
    {
        $sut = new WithEventsHttpClient(
            self::getContainer()->get(EasyEventDispatcherInterface::class),
            new MockHttpClient(new MockResponse(['', new RuntimeException('Some error.')]))
        );
        $response = $sut->request('GET', 'https://eonx.com/');
        $response->getStatusCode();
        $sut->stream($response)
            ->current()
            ->isTimeout();
        $throwable = null;

        try {
            $response->getContent();
        } catch (TransportExceptionInterface $throwable) {
        }
        $response->cancel();

        self::assertInstanceOf(TransportException::class, $throwable);
        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0]['event'];

        self::assertInstanceOf(TransportException::class, $event->getThrowable());
        self::assertSame('Some error.', $event->getThrowable()->getMessage());
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

    public function testDestructedResponseAfterTimeoutChunkAndDataDispatchesEventWithResponse(): void
    {
        $sut = new WithEventsHttpClient(
            self::getContainer()->get(EasyEventDispatcherInterface::class),
            new TimeoutChunkHttpClientStub(new MockResponse(['some-', 'content']))
        );
        $response = $sut->request('GET', 'https://eonx.com/');
        $response->getStatusCode();
        $sut->stream($response, 0)
            ->current()
            ->isTimeout();
        $sut->stream($response, 0)
            ->current();

        unset($response);

        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0]['event'];

        self::assertInstanceOf(ResponseData::class, $event->getResponseData());
        self::assertSame('some-', $event->getResponseData()->getContent());
        self::assertNull($event->getThrowable());
    }

    public function testDisabledEventsDoNotKeepResponseContent(): void
    {
        TestResponseFactory::addResponse(new SimpleTestResponse('https://eonx.com/', responseData: 'some-content'));
        $sut = self::getContainer()->get(HttpClientInterface::class);

        $response = $sut->request('GET', 'https://eonx.com/', [HttpOption::EventsEnabled->value => false]);

        self::assertSame('some-content', $response->getContent());
        self::assertNull($response->getInfo('temp_content'));
    }

    public function testIdleTimeoutDispatchesEventWithThrowable(): void
    {
        $sut = new WithEventsHttpClient(
            self::getContainer()->get(EasyEventDispatcherInterface::class),
            new MockHttpClient(new MockResponse(['', 'some-content']))
        );
        $response = $sut->request('GET', 'https://eonx.com/');
        $throwable = null;

        try {
            $response->getContent();
        } catch (TransportExceptionInterface $throwable) {
        }
        unset($response);

        self::assertInstanceOf(TimeoutException::class, $throwable);
        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0]['event'];

        self::assertNull($event->getResponseData());
        self::assertInstanceOf(TimeoutException::class, $event->getThrowable());
        self::assertSame('Idle timeout reached for "https://eonx.com/".', $event->getThrowable()->getMessage());
    }

    public function testRequestReturnsResponse(): void
    {
        TestResponseFactory::addResponse(new SimpleTestResponse('https://eonx.com/'));
        $sut = self::getContainer()->get(SomeClient::class);

        $sut->makeRequest();

        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());
        self::assertInstanceOf(HttpRequestSentEvent::class, $eventDispatcher->getDispatchedEvents()[0]['event']);

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0]['event'];

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
        self::assertInstanceOf(HttpRequestSentEvent::class, $eventDispatcher->getDispatchedEvents()[0]['event']);

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0]['event'];

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
        $event = $eventDispatcher->getDispatchedEvents()[0]['event'];

        self::assertInstanceOf(ResponseData::class, $event->getResponseData());
        self::assertSame($statusCode, $event->getResponseData()->getStatusCode());
        self::assertNull($event->getThrowable());
    }

    #[DataProvider('provideOwnBufferOptions')]
    public function testResponseWithOwnBufferOptionDispatchesEventWithoutContent(mixed $buffer): void
    {
        TestResponseFactory::addResponse(new SimpleTestResponse('https://eonx.com/', responseData: 'some-content'));
        $sut = self::getContainer()->get(HttpClientInterface::class);
        $content = '';

        $response = $sut->request('GET', 'https://eonx.com/', ['buffer' => $buffer]);
        foreach ($sut->stream($response) as $chunk) {
            $content .= $chunk->getContent();
        }

        self::assertSame('some-content', $content);
        self::assertNull($response->getInfo('temp_content'));
        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());
        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0]['event'];
        self::assertInstanceOf(ResponseData::class, $event->getResponseData());
        self::assertSame('', $event->getResponseData()->getContent());
        self::assertSame(200, $event->getResponseData()->getStatusCode());
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

    public function testStreamReadsBodyAfterTimeoutChunks(): void
    {
        $sut = new WithEventsHttpClient(
            self::getContainer()->get(EasyEventDispatcherInterface::class),
            new TimeoutChunkHttpClientStub(new MockResponse('some-content'))
        );
        $response = $sut->request('GET', 'https://eonx.com/');

        $content = \stream_get_contents(StreamWrapper::createResource($response, $sut));

        self::assertSame('some-content', $content);
        /** @var \EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        self::assertCount(1, $eventDispatcher->getDispatchedEvents());

        /** @var \EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent $event */
        $event = $eventDispatcher->getDispatchedEvents()[0]['event'];

        self::assertInstanceOf(ResponseData::class, $event->getResponseData());
        self::assertSame('some-content', $event->getResponseData()->getContent());
        self::assertNull($event->getThrowable());
    }
}
