<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use EonX\EasyRandom\ValueObject\RandomStringConfig;

interface RandomGeneratorInterface
{
    public function integer(?int $min = null, ?int $max = null): int;

    public function string(RandomStringConfig $randomStringConfig): string;

    public function uuid(): string;
}
