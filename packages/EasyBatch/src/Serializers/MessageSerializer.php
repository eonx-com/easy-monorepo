<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Serializers;

use EonX\EasyBatch\Interfaces\MessageSerializerInterface;

final class MessageSerializer implements MessageSerializerInterface
{
    public function serialize(object $message): string
    {
        $body = \addslashes(\serialize($message));

        if (\preg_match('//u', $body) === false) {
            $body = \base64_encode($body);
        }

        return $body;
    }

    public function unserialize(string $message): object
    {
        if (\str_contains($message, '}') === false) {
            /** @var string $message */
            $message = \base64_decode($message, true);
        }

        return \unserialize(\stripslashes($message));
    }
}
