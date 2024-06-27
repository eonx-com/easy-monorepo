<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Loader;

interface CoverageLoaderInterface
{
    public function load(string $path): string;
}
