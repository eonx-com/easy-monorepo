<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\ValueObject;

final readonly class CoverageReport
{
    /**
     * @var string[]
     */
    private array $violations;

    /**
     * @param string[] $violations
     */
    public function __construct(
        private float $coverage,
        ?array $violations = null,
    ) {
        $this->violations = $violations ?? [];
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
        return \count($this->violations) !== 0;
    }
}
