<?php

declare(strict_types=1);

namespace EonX\EasyTest\Coverage\DataTransferObjects;

final class CoverageReportDto
{
    /**
     * @var float
     */
    private $coverage;

    /**
     * @var string[]
     */
    private $violations;

    /**
     * @param string[] $violations
     */
    public function __construct(float $coverage, array $violations = [])
    {
        $this->coverage = $coverage;
        $this->violations = $violations;
    }

    public function getCoverage(): float
    {
        return $this->coverage;
    }

    /**
     * @return string[]
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    public function hasViolations(): bool
    {
        return empty($this->violations) === false;
    }
}
