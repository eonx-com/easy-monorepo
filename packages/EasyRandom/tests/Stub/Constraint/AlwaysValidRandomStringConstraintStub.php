<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Stub\Constraint;

use EonX\EasyRandom\Constraint\RandomStringConstraintInterface;

final class AlwaysValidRandomStringConstraintStub implements RandomStringConstraintInterface
{
    public function isValid(string $randomString): bool
    {
        return true;
    }
}
