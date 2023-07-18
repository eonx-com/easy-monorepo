<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Interfaces;

interface RandomGeneratorInterface
{
    public function integer(?int $min = null, ?int $max = null): int;

    public function string(int $length): RandomStringInterface;

    public function uuid(): string;

    public function uuidV4(): string;
}
