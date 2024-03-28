<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generators;

use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use RuntimeException;

final class DefaultUuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        throw new RuntimeException('Install `symfony/uid` to use built-in UUID generator or implement your own.');
    }
}
