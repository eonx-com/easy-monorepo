<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Generators;

use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use Symfony\Component\Uid\UuidV6;

final class SymfonyUuidV6Generator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return (string)(new UuidV6());
    }
}
