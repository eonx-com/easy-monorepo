<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Locator;

use EonX\EasyTest\Coverage\Resolver\CoverageResolverInterface;

interface CoverageResolverLocatorInterface
{
    public function getCoverageResolver(string $filePath): CoverageResolverInterface;
}
