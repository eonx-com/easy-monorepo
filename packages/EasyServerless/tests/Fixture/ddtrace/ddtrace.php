<?php
declare(strict_types=1);

/**
 * Runtime stand-in of the ddtrace extension symbols used by RootSpanHelper and EnvelopeTracer.
 * Only require it in tests running in a separate process, as functions cannot be undeclared.
 * PHPStan also discovers these symbols here, as the extension is not loaded during the analysis.
 */

namespace DDTrace {
    class SpanData
    {
        /**
         * @var \DDTrace\SpanLink[]
         */
        public array $links = [];

        /**
         * @var array<string, string>
         */
        public array $meta = [];

        /**
         * @var array<string, float|int>
         */
        public array $metrics = [];

        public ?string $name = '';

        public ?string $resource = '';

        public ?string $type = '';
    }

    final class RootSpanData extends SpanData
    {
        public string $parentId = '';

        public string $traceId = '';
    }

    final class SpanLink
    {
        public string $spanId = '';

        public string $traceId = '';

        public static function fromHeaders(array|callable $headersOrCallback): self
        {
            $headers = \is_array($headersOrCallback) ? $headersOrCallback : [];
            $spanLink = new self();
            $spanLink->spanId = (string) ($headers['x-datadog-parent-id'] ?? '');
            $spanLink->traceId = (string) ($headers['x-datadog-trace-id'] ?? '');

            return $spanLink;
        }
    }

    /**
     * Open spans of the stand-in tracer, the last one is the active span.
     */
    final class SpanStackStub
    {
        /**
         * @var \DDTrace\SpanData[]
         */
        public static array $spans = [];
    }

    function active_span(): ?SpanData
    {
        return SpanStackStub::$spans[\array_key_last(SpanStackStub::$spans)] ?? null;
    }

    function close_span(float $finishTime = 0): ?false
    {
        \array_pop(SpanStackStub::$spans);

        return null;
    }

    function close_spans_until(?SpanData $span): false|int
    {
        // Like the extension, nothing is closed when the span is not open
        if ($span !== null && \in_array($span, SpanStackStub::$spans, true) === false) {
            return false;
        }

        $closedSpans = 0;

        while (SpanStackStub::$spans !== [] && \DDTrace\active_span() !== $span) {
            \array_pop(SpanStackStub::$spans);
            $closedSpans++;
        }

        return $closedSpans;
    }

    function consume_distributed_tracing_headers(array|callable|null $headersOrCallback): void
    {
        $rootSpan = \DDTrace\root_span();

        if ($rootSpan instanceof RootSpanData && \is_array($headersOrCallback)) {
            $rootSpan->parentId = (string) ($headersOrCallback['x-datadog-parent-id'] ?? '');
            $rootSpan->traceId = (string) ($headersOrCallback['x-datadog-trace-id'] ?? '');
        }
    }

    /**
     * Returns the span of the last start_trace_span() call still open, as it starts a new trace on a new span stack,
     * or a span standing for the root span of the request otherwise.
     */
    function root_span(): ?SpanData
    {
        static $rootSpan = null;

        foreach (\array_reverse(SpanStackStub::$spans) as $span) {
            if ($span instanceof RootSpanData) {
                return $span;
            }
        }

        return $rootSpan ??= new SpanData();
    }

    function start_span(float $startTime = 0): SpanData|false
    {
        $span = new SpanData();
        SpanStackStub::$spans[] = $span;

        return $span;
    }

    function start_trace_span(float $startTime = 0): RootSpanData
    {
        $span = new RootSpanData();
        SpanStackStub::$spans[] = $span;

        return $span;
    }
}

namespace {
    function dd_trace_env_config(string $envName): mixed
    {
        $value = \getenv($envName);

        return $value === false ? true : \filter_var($value, \FILTER_VALIDATE_BOOL);
    }
}
