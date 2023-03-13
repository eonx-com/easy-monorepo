<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Interfaces;

interface UuidV6GeneratorInterface
{
    public function generate(): string;
}
