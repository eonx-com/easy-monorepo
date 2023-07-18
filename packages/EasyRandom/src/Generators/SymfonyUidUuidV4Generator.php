<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Generators;

use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use Symfony\Component\Uid\UuidV4;

final class SymfonyUidUuidV4Generator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return (string)(new UuidV4());
    }
}
