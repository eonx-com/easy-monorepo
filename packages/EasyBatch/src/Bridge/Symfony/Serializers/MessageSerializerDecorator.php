<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Serializers;

use EonX\EasyBatch\Interfaces\MessageSerializerInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\AckStamp;

final class MessageSerializerDecorator implements MessageSerializerInterface
{
    public function __construct(
        private MessageSerializerInterface $decorated,
    ) {
    }

    public function serialize(object $message): string
    {
        if ($message instanceof HandlerFailedException) {
            $envelope = $message->getEnvelope()
                ->withoutAll(AckStamp::class);
            $message = new HandlerFailedException($envelope, $message->getWrappedExceptions());
        }

        return $this->decorated->serialize($message);
    }

    public function unserialize(string $message): object
    {
        return $this->decorated->unserialize($message);
    }
}
