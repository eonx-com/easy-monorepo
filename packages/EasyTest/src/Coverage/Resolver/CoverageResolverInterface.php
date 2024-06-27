<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Resolver;

use EonX\EasyTest\Coverage\ValueObject\CoverageReport;

interface CoverageResolverInterface
{
    public function resolve(string $coverageOutput): CoverageReport;
}
