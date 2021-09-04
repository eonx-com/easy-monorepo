<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class NotSupportedMessageMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if (($message instanceof NotSupportedMessage) === false) {
            return $stack->next()
                ->handle($envelope, $stack);
        }

        throw new UnrecoverableMessageHandlingException('message not supported', 0, $message->getThrowable());
    }
}
