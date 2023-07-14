<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer;

use EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces\QueueEnvelopeInterface;
use Throwable;

final class NotSupportedMessage
{
    public function __construct(
        private QueueEnvelopeInterface $envelope,
        private ?Throwable $throwable = null,
    ) {
    }

    public static function create(QueueEnvelopeInterface $envelope, ?Throwable $throwable = null): self
    {
        return new self($envelope, $throwable);
    }

    public function getEnvelope(): QueueEnvelopeInterface
    {
        return $this->envelope;
    }

    public function getThrowable(): ?Throwable
    {
        return $this->throwable;
    }
}
