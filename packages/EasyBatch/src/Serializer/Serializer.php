<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Serializer;

use EonX\EasyBatch\Interfaces\SerializerInterface;

class Serializer implements SerializerInterface
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
        if (\strpos($message, '}', -1) === false) {
            /** @var string $message */
            $message = \base64_decode($message, true);
        }

        return \unserialize(\stripslashes($message));
    }
}
