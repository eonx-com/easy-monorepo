<?php
declare(strict_types=1);

namespace EonX\EasyTest\Interfaces;

interface CoverageResolverLocatorInterface
{
    public function getCoverageResolver(string $filePath): CoverageResolverInterface;
}
