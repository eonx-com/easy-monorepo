<?php

declare(strict_types=1);

namespace EonX\EasyRandom\UuidV4;

use EonX\EasyRandom\Interfaces\UuidV4GeneratorInterface;
use Ramsey\Uuid\Uuid;

final class RamseyUuidV4Generator implements UuidV4GeneratorInterface
{
    public function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
