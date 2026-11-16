<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Application\Common\HttpClient;

use DateTimeInterface;
use EonX\EasyHttpClient\Common\Enum\HttpOption;
use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use EonX\EasyHttpClient\Common\ValueObject\ResponseData;
use EonX\EasyHttpClient\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyHttpClient\Tests\Fixture\App\Client\SomeClient;
use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use EonX\EasyTest\HttpClient\Response\SimpleTestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
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

    public function testDisabledEventsDoNotKeepResponseContent(): void
    {
        TestResponseFactory::addResponse(new SimpleTestResponse('https://eonx.com/', responseData: 'some-content'));
        $sut = self::getContainer()->get(HttpClientInterface::class);

        $response = $sut->request('GET', 'https://eonx.com/', [HttpOption::EventsEnabled->value => false]);

        self::assertSame('some-content', $response->getContent());
        self::assertNull($response->getInfo('temp_content'));
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
}
