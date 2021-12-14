<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\Serializer;

use EonX\EasyBatch\Interfaces\SerializerInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class SerializerDecorator implements SerializerInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\SerializerInterface
     */
    private $decorated;

    public function __construct(SerializerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function serialize(object $message): string
    {
        if ($message instanceof HandlerFailedException) {
            // @todo Use AckStamp::class when drop support Symfony lower that 5.4
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
