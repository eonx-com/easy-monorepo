<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer;

use EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces\MessageBodyDecoderInterface;

final class JsonMessageBodyDecoder implements MessageBodyDecoderInterface
{
    public function decode(string $body): ?array
    {
        return \json_decode($body, true);
    }
}
