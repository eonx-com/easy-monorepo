<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Constraint;

interface RandomStringConstraintInterface
{
    public function isValid(string $randomString): bool;
}
