<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use EonX\EasyRandom\ValueObject\RandomString;

interface RandomGeneratorInterface
{
    public function integer(?int $min = null, ?int $max = null): int;

    public function string(int $length): RandomString;

    public function uuid(): string;
}
