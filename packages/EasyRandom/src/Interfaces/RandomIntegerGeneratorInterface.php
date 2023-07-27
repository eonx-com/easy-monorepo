<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Interfaces;

interface RandomIntegerGeneratorInterface
{
    public function generate(?int $min = null, ?int $max = null): int;
}
