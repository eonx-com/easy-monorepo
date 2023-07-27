<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Interfaces;

interface RandomStringConstraintInterface
{
    public function isValid(string $randomString): bool;
}
