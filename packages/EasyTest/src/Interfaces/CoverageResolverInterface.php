<?php

declare(strict_types=1);

namespace EonX\EasyTest\Interfaces;

use EonX\EasyTest\Coverage\DataTransferObjects\CoverageReportDto;

interface CoverageResolverInterface
{
    public function resolve(string $coverageOutput): CoverageReportDto;
}
