<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use EonX\EasyRandom\ValueObject\RandomStringInterface;

interface RandomGeneratorInterface
{
    public function integer(?int $min = null, ?int $max = null): int;

    public function string(int $length): RandomStringInterface;

    public function uuid(): string;
}
