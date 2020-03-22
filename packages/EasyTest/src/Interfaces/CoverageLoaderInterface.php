<?php

declare(strict_types=1);

namespace EonX\EasyTest\Interfaces;

interface CoverageLoaderInterface
{
    public function load(string $path): string;
}
