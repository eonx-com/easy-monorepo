<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Decoder;

use RuntimeException;

final class JsonMessageBodyDecoder implements MessageBodyDecoderInterface
{
    public function decode(string $body): ?array
    {
        $result = \json_decode($body, true);

        if (\is_array($result) === false && $result !== null) {
            throw new RuntimeException('Failed to decode message body.');
        }

        return $result;
    }
}
