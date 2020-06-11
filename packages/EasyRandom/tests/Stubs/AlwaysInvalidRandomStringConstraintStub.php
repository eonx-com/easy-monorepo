<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Stubs;

use EonX\EasyRandom\Interfaces\RandomStringConstraintInterface;

final class AlwaysInvalidRandomStringConstraintStub implements RandomStringConstraintInterface
{
    public function isValid(string $randomString): bool
    {
        return false;
    }
}
