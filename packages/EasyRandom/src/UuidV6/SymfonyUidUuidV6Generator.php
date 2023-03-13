<?php

declare(strict_types=1);

namespace EonX\EasyRandom\UuidV6;

use EonX\EasyRandom\Interfaces\UuidV6GeneratorInterface;
use Symfony\Component\Uid\UuidV6;

final class SymfonyUidUuidV6Generator implements UuidV6GeneratorInterface
{
    public function generate(): string
    {
        return (string)(new UuidV6());
    }
}
