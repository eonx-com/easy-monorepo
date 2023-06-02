<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer;

use EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces\MessageBodyDecoderInterface;
use EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces\MessageObjectFactoryInterface;
use RuntimeException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class MessageSerializer implements SerializerInterface
{
    /**
     * @var string
     */
    private const HEADER_RETRY = 'retry';

    /**
     * @var string
     */
    private const KEY_BODY = 'body';

    /**
     * @var string
     */
    private const KEY_HEADERS = 'headers';

    public function __construct(
        private readonly MessageBodyDecoderInterface $bodyDecoder,
        private readonly MessageObjectFactoryInterface $messageFactory,
    ) {
    }

    /**
     * @param mixed[] $encodedEnvelope
     */
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
                $stamps[] = new RedeliveryStamp((int)$queueEnvelope->getHeader(self::HEADER_RETRY));
            }

            return Envelope::wrap($message, $stamps);
        } catch (\Throwable $throwable) {
            return Envelope::wrap(NotSupportedMessage::create($queueEnvelope, $throwable), $stamps);
        }
    }

    /**
     * @return mixed[]
     */
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
