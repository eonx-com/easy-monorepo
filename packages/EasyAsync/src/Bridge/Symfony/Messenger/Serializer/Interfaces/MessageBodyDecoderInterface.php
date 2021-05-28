<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\Messenger\Serializer\Interfaces;

interface MessageBodyDecoderInterface
{
    /**
     * @return mixed[]
     */
    public function decode(string $body): ?array;
}
