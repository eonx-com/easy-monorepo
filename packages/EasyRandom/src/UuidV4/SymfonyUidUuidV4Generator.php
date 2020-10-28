<?php

declare(strict_types=1);

namespace EonX\EasyRandom\UuidV4;

use EonX\EasyRandom\Interfaces\UuidV4GeneratorInterface;
use Symfony\Component\Uid\UuidV4;

final class SymfonyUidUuidV4Generator implements UuidV4GeneratorInterface
{
    public function generate(): string
    {
        return (string)(new UuidV4());
    }
}
