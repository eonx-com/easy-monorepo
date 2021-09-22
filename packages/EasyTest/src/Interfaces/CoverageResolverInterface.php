<?php

declare(strict_types=1);

namespace EonX\EasyTest\Interfaces;

use EonX\EasyTest\Coverage\DataTransferObject\CoverageReportDto;

interface CoverageResolverInterface
{
    public function resolve(string $coverageOutput): CoverageReportDto;
}
