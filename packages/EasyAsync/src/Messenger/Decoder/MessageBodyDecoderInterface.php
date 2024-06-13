<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Decoder;

interface MessageBodyDecoderInterface
{
    public function decode(string $body): ?array;
}
