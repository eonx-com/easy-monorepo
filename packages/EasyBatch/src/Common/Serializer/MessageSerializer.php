<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Serializer;

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

        /** @var object $result */
        $result = \unserialize(\stripslashes($message));

        return $result;
    }
}
