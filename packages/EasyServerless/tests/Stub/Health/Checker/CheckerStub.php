<?php
declare(strict_types=1);

namespace Stub\Health\Checker;

use EonX\EasyServerless\Health\Checker\HealthCheckerInterface;
use EonX\EasyServerless\Health\ValueObject\HealthCheckResult;
use Throwable;

final readonly class CheckerStub implements HealthCheckerInterface
{
    public function __construct(
        private string $name,
        private ?bool $isHealthy = null,
        private ?Throwable $throwable = null,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function check(): HealthCheckResult
    {
        if ($this->throwable !== null) {
            throw $this->throwable;
        }

        return new HealthCheckResult($this->isHealthy ?? true);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
