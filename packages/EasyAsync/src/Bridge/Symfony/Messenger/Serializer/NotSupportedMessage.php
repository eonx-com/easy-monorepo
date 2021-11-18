<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer;

use EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces\QueueEnvelopeInterface;
use Throwable;

final class NotSupportedMessage
{
    /**
     * @var \EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces\QueueEnvelopeInterface
     */
    private $envelope;

    /**
     * @var null|\Throwable
     */
    private $throwable;

    public function __construct(QueueEnvelopeInterface $envelope, ?Throwable $throwable = null)
    {
        $this->envelope = $envelope;
        $this->throwable = $throwable;
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
