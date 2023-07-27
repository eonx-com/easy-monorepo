<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces;

interface MessageBodyDecoderInterface
{
    public function decode(string $body): ?array;
}
