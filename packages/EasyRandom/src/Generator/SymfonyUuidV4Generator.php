<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use Symfony\Component\Uid\UuidV4;

final class SymfonyUuidV4Generator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return (string)(new UuidV4());
    }
}