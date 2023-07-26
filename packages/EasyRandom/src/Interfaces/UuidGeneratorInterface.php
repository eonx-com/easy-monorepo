<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Interfaces;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
