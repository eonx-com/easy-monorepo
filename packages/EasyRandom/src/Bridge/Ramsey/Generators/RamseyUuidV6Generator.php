<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Ramsey\Generators;

use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use Ramsey\Uuid\Uuid;

final class RamseyUuidV6Generator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::uuid6()->toString();
    }
}
