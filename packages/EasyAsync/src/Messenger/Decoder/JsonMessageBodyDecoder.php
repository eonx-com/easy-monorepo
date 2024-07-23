<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Decoder;

final class JsonMessageBodyDecoder implements MessageBodyDecoderInterface
{
    public function decode(string $body): ?array
    {
        return \json_decode($body, true);
    }
}
