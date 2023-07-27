<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Stubs;

use EonX\EasyRandom\Interfaces\RandomStringConstraintInterface;

final class AlwaysValidRandomStringConstraintStub implements RandomStringConstraintInterface
{
    public function isValid(string $randomString): bool
    {
        return true;
    }
}
