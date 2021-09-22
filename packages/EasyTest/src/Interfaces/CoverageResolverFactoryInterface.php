<?php

declare(strict_types=1);

namespace EonX\EasyTest\Interfaces;

interface CoverageResolverFactoryInterface
{
    public function create(string $filePath): CoverageResolverInterface;
}
