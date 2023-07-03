<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Serializers;

use EonX\EasyBatch\Interfaces\MessageSerializerInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\AckStamp;

final class MessageSerializerDecorator implements MessageSerializerInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\MessageSerializerInterface
     */
    private $decorated;

    public function __construct(MessageSerializerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function serialize(object $message): string
    {
        if ($message instanceof HandlerFailedException) {
            $envelope = $message->getEnvelope()
                ->withoutAll(AckStamp::class);
            $message = new HandlerFailedException($envelope, $message->getNestedExceptions());
        }

        return $this->decorated->serialize($message);
    }

    public function unserialize(string $message): object
    {
        return $this->decorated->unserialize($message);
    }
}
