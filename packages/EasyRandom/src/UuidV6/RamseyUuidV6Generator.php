<?php

declare(strict_types=1);

namespace EonX\EasyRandom\UuidV6;

use EonX\EasyRandom\Interfaces\UuidV6GeneratorInterface;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
use Ramsey\Uuid\Uuid;

final class RamseyUuidV6Generator implements UuidV6GeneratorInterface
{
    public function generate(): string
    {
        $factory = Uuid::getFactory();

        if (\method_exists($factory, 'uuid6')) {
            return $factory->uuid6()
                ->toString();
        }

        throw new UnsupportedOperationException('The provided factory does not support the uuid6() method');
    }
}
