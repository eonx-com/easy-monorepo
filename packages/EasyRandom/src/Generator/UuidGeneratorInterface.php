<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
