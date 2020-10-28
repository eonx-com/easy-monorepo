<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Interfaces;

interface UuidV4GeneratorInterface
{
    public function generate(): string;
}
