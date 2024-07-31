<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Resolver;

use EonX\EasyLogging\Enum\BugsnagSeverity;

interface BugsnagSeverityResolverInterface
{
    public function resolve(int $level): BugsnagSeverity;
}
