<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Health\Checker;

use EonX\EasyServerless\Health\ValueObject\HealthCheckResult;

final class SanityChecker implements HealthCheckerInterface
{
    public function check(): HealthCheckResult
    {
        // Simply make sure the application is running
        return new HealthCheckResult(true);
    }

    public function getName(): string
    {
        return 'sanity';
    }
}
