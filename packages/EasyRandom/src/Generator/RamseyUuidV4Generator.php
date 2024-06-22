<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use Ramsey\Uuid\Uuid;

final class RamseyUuidV4Generator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
