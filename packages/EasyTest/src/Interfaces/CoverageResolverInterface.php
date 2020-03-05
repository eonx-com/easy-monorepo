<?php
declare(strict_types=1);

namespace EonX\EasyTest\Interfaces;

interface CoverageResolverInterface
{
    public function resolve(string $coverageOutput): float;
}
