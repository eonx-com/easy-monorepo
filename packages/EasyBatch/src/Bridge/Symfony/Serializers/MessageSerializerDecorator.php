<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Serializers;

use EonX\EasyBatch\Interfaces\MessageSerializerInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

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
            // @todo Use AckStamp::class when drop support Symfony lower than 5.4
            $envelope = $message->getEnvelope()
                ->withoutAll('Symfony\Component\Messenger\Stamp\AckStamp');
            $message = new HandlerFailedException($envelope, $message->getNestedExceptions());
        }

        return $this->decorated->serialize($message);
    }

    public function unserialize(string $message): object
    {
        return $this->decorated->unserialize($message);
    }
}
