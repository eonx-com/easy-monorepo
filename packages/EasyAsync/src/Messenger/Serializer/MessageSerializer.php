<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Serializer;

use EonX\EasyAsync\Messenger\Decoder\MessageBodyDecoderInterface;
use EonX\EasyAsync\Messenger\Envelope\QueueEnvelope;
use EonX\EasyAsync\Messenger\Factory\MessageObjectFactoryInterface;
use EonX\EasyAsync\Messenger\Message\NotSupportedMessage;
use EonX\EasyAsync\Messenger\Stamp\OriginalMessageStamp;
use RuntimeException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Throwable;

final readonly class MessageSerializer implements SerializerInterface
{
    private const HEADER_RETRY = 'retry';

    private const KEY_BODY = 'body';

    private const KEY_HEADERS = 'headers';

    public function __construct(
        private MessageBodyDecoderInterface $bodyDecoder,
        private MessageObjectFactoryInterface $messageFactory,
    ) {
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        $originalBody = $encodedEnvelope[self::KEY_BODY] ?? '';
        $body = $this->bodyDecoder->decode($originalBody);
        $headers = $encodedEnvelope[self::KEY_HEADERS] ?? [];
        $queueEnvelope = QueueEnvelope::create($originalBody, $headers, $body);
        $stamps = [OriginalMessageStamp::create($originalBody, $headers)];

        try {
            $message = $this->messageFactory->createMessage($queueEnvelope);

            if ($queueEnvelope->getHeader(self::HEADER_RETRY) !== null) {
                /** @var scalar $retry */
                $retry = $queueEnvelope->getHeader(self::HEADER_RETRY);
                $stamps[] = new RedeliveryStamp((int)$retry);
            }

            return Envelope::wrap($message, $stamps);
        } catch (Throwable $throwable) {
            return Envelope::wrap(NotSupportedMessage::create($queueEnvelope, $throwable), $stamps);
        }
    }

    public function encode(Envelope $envelope): array
    {
        $originalStamp = $envelope->last(OriginalMessageStamp::class);

        if (($originalStamp instanceof OriginalMessageStamp) === false) {
            throw new RuntimeException('Invalid envelope missing original message stamp');
        }

        /**
         * If envelope gets here it's because it failed, and we want to retry it.
         * We need to handle the according stamps to avoid retrying the message infinitely.
         */
        $headers = $originalStamp->getHeaders();
        $redeliveryStamp = $envelope->last(RedeliveryStamp::class);

        if ($redeliveryStamp instanceof RedeliveryStamp) {
            $headers[self::HEADER_RETRY] = $redeliveryStamp->getRetryCount();
        }

        return [
            self::KEY_BODY => $originalStamp->getBody(),
            self::KEY_HEADERS => $headers,
        ];
    }
}
