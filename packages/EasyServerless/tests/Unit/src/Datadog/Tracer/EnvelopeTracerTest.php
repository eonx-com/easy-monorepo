<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Datadog\Tracer;

use DDTrace\RootSpanData;
use EonX\EasyServerless\Datadog\Tracer\EnvelopeTracer;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\SerializerStamp;

use function DDTrace\active_span;
use function DDTrace\start_span;

final class EnvelopeTracerTest extends AbstractUnitTestCase
{
    private const array HEADERS = [
        'x-datadog-parent-id' => '2222',
        'x-datadog-sampling-priority' => '1',
        'x-datadog-trace-id' => '1111',
    ];

    /**
     * @see testStartSpanIsNoOpWithoutDatadogContext
     */
    public static function provideEnvelopeWithoutDatadogContextData(): iterable
    {
        yield 'no SerializerStamp' => [new Envelope(new stdClass())];
        yield 'SerializerStamp without datadog context' => [
            new Envelope(new stdClass(), [new SerializerStamp(['groups' => ['group']])]),
        ];
        yield 'empty datadog context' => [new Envelope(new stdClass(), [new SerializerStamp(['datadog' => []])])];
        yield 'datadog context is not an array' => [
            new Envelope(new stdClass(), [new SerializerStamp(['datadog' => 'headers'])]),
        ];
    }

    #[RunInSeparateProcess]
    public function testFinishSpanClosesSpanAndSpansLeftOpen(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../../../Fixture/ddtrace/ddtrace.php';
        $sut = new EnvelopeTracer();
        $span = $sut->startSpan(
            new Envelope(new stdClass(), [new SerializerStamp(['datadog' => self::HEADERS])]),
            'async'
        );
        start_span();

        $sut->finishSpan($span);

        self::assertNotNull($span);
        self::assertNull(active_span());
    }

    #[RunInSeparateProcess]
    public function testFinishSpanKeepsPreviousTraceActive(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../../../Fixture/ddtrace/ddtrace.php';
        $previousSpan = start_span();
        $sut = new EnvelopeTracer();
        $span = $sut->startSpan(
            new Envelope(new stdClass(), [new SerializerStamp(['datadog' => self::HEADERS])]),
            'async'
        );

        $sut->finishSpan($span);

        self::assertNotSame($previousSpan, $span);
        self::assertSame($previousSpan, active_span());
    }

    public function testFinishSpanWithoutSpanIsNoOp(): void
    {
        $sut = new EnvelopeTracer();

        $sut->finishSpan(null);

        $this->addToAssertionCount(1);
    }

    #[RunInSeparateProcess]
    public function testStartSpanContinuesTraceOfSender(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../../../Fixture/ddtrace/ddtrace.php';
        $envelope = new Envelope(new stdClass(), [new SerializerStamp(['datadog' => self::HEADERS])]);
        $sut = new EnvelopeTracer();

        $span = $sut->startSpan($envelope, 'async');

        self::assertInstanceOf(RootSpanData::class, $span);
        self::assertSame($span, active_span());
        self::assertSame('symfony.messenger.consume', $span->name);
        self::assertSame('async -> stdClass', $span->resource);
        self::assertSame('queue', $span->type);
        self::assertSame('1111', $span->traceId);
        self::assertSame('2222', $span->parentId);
        self::assertSame([], $span->links);
    }

    #[RunInSeparateProcess]
    public function testStartSpanIsNoOpWhenTracingIsDisabled(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../../../Fixture/ddtrace/ddtrace.php';
        \putenv('DD_TRACE_ENABLED=false');
        $envelope = new Envelope(new stdClass(), [new SerializerStamp(['datadog' => self::HEADERS])]);
        $sut = new EnvelopeTracer();

        $span = $sut->startSpan($envelope, 'async');

        self::assertNull($span);
        self::assertNull(active_span());
    }

    #[DataProvider('provideEnvelopeWithoutDatadogContextData')]
    #[RunInSeparateProcess]
    public function testStartSpanIsNoOpWithoutDatadogContext(Envelope $envelope): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../../../Fixture/ddtrace/ddtrace.php';
        $sut = new EnvelopeTracer();

        $span = $sut->startSpan($envelope, 'async');

        self::assertNull($span);
        self::assertNull(active_span());
    }

    public function testStartSpanIsNoOpWithoutDdTrace(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        $envelope = new Envelope(new stdClass(), [new SerializerStamp(['datadog' => self::HEADERS])]);
        $sut = new EnvelopeTracer();

        $span = $sut->startSpan($envelope, 'async');

        self::assertNull($span);
    }

    #[RunInSeparateProcess]
    public function testStartSpanLinksTraceOfSenderWhenDistributedTracingIsDisabled(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../../../Fixture/ddtrace/ddtrace.php';
        \putenv('DD_TRACE_SYMFONY_MESSENGER_DISTRIBUTED_TRACING=false');
        $envelope = new Envelope(new stdClass(), [new SerializerStamp(['datadog' => self::HEADERS])]);
        $sut = new EnvelopeTracer();

        $span = $sut->startSpan($envelope, 'async');

        self::assertInstanceOf(RootSpanData::class, $span);
        self::assertSame($span, active_span());
        self::assertSame('', $span->traceId);
        self::assertSame('', $span->parentId);
        self::assertCount(1, $span->links);
        self::assertSame('1111', $span->links[0]->traceId);
        self::assertSame('2222', $span->links[0]->spanId);
    }

    #[RunInSeparateProcess]
    public function testStartSpanUsesFirstSerializerStampWithDatadogContext(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../../../Fixture/ddtrace/ddtrace.php';
        $envelope = new Envelope(new stdClass(), [
            new SerializerStamp(['groups' => ['group']]),
            new SerializerStamp(['datadog' => self::HEADERS]),
            new SerializerStamp([
                'datadog' => [
                    'x-datadog-parent-id' => '4444',
                    'x-datadog-trace-id' => '3333',
                ],
            ]),
        ]);
        $sut = new EnvelopeTracer();

        $span = $sut->startSpan($envelope, 'async');

        self::assertInstanceOf(RootSpanData::class, $span);
        self::assertSame('1111', $span->traceId);
        self::assertSame('2222', $span->parentId);
    }
}
