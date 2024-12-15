<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Decoder;

final class JsonMessageBodyDecoder implements MessageBodyDecoderInterface
{
    public function decode(string $body): ?array
    {
        /** @var array|null $result */
        $result = \json_decode($body, true);

        return $result;
    }
}
