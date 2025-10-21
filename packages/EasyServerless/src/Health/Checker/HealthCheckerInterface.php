<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Health\Checker;

use EonX\EasyServerless\Health\ValueObject\HealthCheckResult;

interface HealthCheckerInterface
{
    public function check(): HealthCheckResult;

    public function getName(): string;
}
