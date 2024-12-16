<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Serializer;

use RuntimeException;

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
        if (\str_ends_with($message, '}') === false) {
            /** @var string $message */
            $message = \base64_decode($message, true);
        }

        $result = \unserialize(\stripslashes($message));

        if (\is_object($result) === false) {
            throw new RuntimeException('Failed to unserialize message.');
        }

        return $result;
    }
}
