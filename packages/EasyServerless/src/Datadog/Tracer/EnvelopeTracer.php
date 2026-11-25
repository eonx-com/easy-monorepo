<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Datadog\Tracer;

use DDTrace\SpanData;
use DDTrace\SpanLink;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\SerializerStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;

use function DDTrace\active_span;
use function DDTrace\close_span;
use function DDTrace\close_spans_until;
use function DDTrace\consume_distributed_tracing_headers;
use function DDTrace\start_trace_span;

/**
 * Connects the trace of a message consumed by the serverless SqsHandler to the trace that sent it.
 *
 * The Symfony Messenger integration of the ddtrace extension adds the Datadog context of the sender to the envelope
 * as a SerializerStamp, but only reads it back when Symfony Worker::handleMessage() consumes the message, which
 * the SqsHandler does not call. Like the integration, it continues the sender trace, or only links to it when
 * DD_TRACE_SYMFONY_MESSENGER_DISTRIBUTED_TRACING is false.
 */
final class EnvelopeTracer
{
    private const string COMPONENT = 'symfonymessenger';

    private const string DATADOG_CONTEXT_KEY = 'datadog';

    private const string SPAN_NAME = 'symfony.messenger.consume';

    private const string SPAN_TYPE = 'queue';

    /**
     * Ends the span started by startSpan(), and the spans started in it and left open.
     * The previous trace, if any, is active again.
     */
    public function finishSpan(?SpanData $span): void
    {
        if ($span === null) {
            return;
        }

        close_spans_until($span);

        if (active_span() === $span) {
            close_span();
        }
    }

    /**
     * Starts a new trace for the message, with the Datadog context of the sender.
     * Returns null and starts nothing when the ddtrace extension is not loaded, tracing is disabled,
     * or the envelope has no Datadog context.
     */
    public function startSpan(Envelope $envelope, string $transportName): ?SpanData
    {
        $headers = $this->resolveDistributedTracingHeaders($envelope);

        // The functions only exist when the ddtrace extension is loaded
        if ($headers === null
            || \function_exists('DDTrace\start_trace_span') === false
            || \dd_trace_env_config('DD_TRACE_ENABLED') !== true) {
            return null;
        }

        // Each message gets its own trace, as a Lambda invocation handles a batch of messages: consuming the context
        // in the active trace would put all of them in one trace, and with no active trace the tracer would keep
        // the context for all the next traces of the process
        $span = start_trace_span();
        $span->name = self::SPAN_NAME;
        $span->resource = \sprintf('%s -> %s', $transportName, $envelope->getMessage()::class);
        $span->type = self::SPAN_TYPE;
        $this->setTags($span, $envelope, $transportName);

        if (\dd_trace_env_config('DD_TRACE_SYMFONY_MESSENGER_DISTRIBUTED_TRACING') === true) {
            // Applies to the root span of the active trace, which is the span started above
            consume_distributed_tracing_headers($headers);

            return $span;
        }

        $span->links[] = SpanLink::fromHeaders($headers);

        return $span;
    }

    /**
     * Same lookup as the Symfony Messenger integration of the ddtrace extension: the first SerializerStamp
     * with a "datadog" context.
     */
    private function resolveDistributedTracingHeaders(Envelope $envelope): ?array
    {
        foreach ($envelope->all(SerializerStamp::class) as $serializerStamp) {
            /** @var \Symfony\Component\Messenger\Stamp\SerializerStamp $serializerStamp */
            $context = $serializerStamp->getContext();

            if (isset($context[self::DATADOG_CONTEXT_KEY])) {
                $headers = $context[self::DATADOG_CONTEXT_KEY];

                return \is_array($headers) && $headers !== [] ? $headers : null;
            }
        }

        return null;
    }

    /**
     * Same tags as the Symfony Messenger integration of the ddtrace extension sets on the span of a consumed message.
     * They are set through $meta and $metrics, which the extension 2.x still accepts as a view onto $attributes.
     */
    private function setTags(SpanData $span, Envelope $envelope, string $transportName): void
    {
        $span->meta['component'] = self::COMPONENT;
        $span->meta['messaging.destination'] = $transportName;
        $span->meta['messaging.destination_kind'] = 'queue';
        $span->meta['messaging.operation'] = 'receive';
        $span->meta['messaging.symfony.message'] = $envelope->getMessage()::class;
        $span->meta['messaging.system'] = 'symfony';
        $span->meta['span.kind'] = 'consumer';

        $messageId = $envelope->last(TransportMessageIdStamp::class)?->getId();
        if (\is_int($messageId) || \is_string($messageId)) {
            $span->meta['messaging.message_id'] = (string) $messageId;
        }

        $retryCount = $envelope->last(RedeliveryStamp::class)?->getRetryCount();
        if ($retryCount !== null) {
            $span->metrics['messaging.symfony.retry_count'] = $retryCount;
        }
    }
}
