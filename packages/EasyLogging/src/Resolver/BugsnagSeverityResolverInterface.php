<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Resolver;

use EonX\EasyLogging\Enum\BugsnagSeverity;
use Monolog\Level;

interface BugsnagSeverityResolverInterface
{
    public function resolve(Level $level): BugsnagSeverity;
}
