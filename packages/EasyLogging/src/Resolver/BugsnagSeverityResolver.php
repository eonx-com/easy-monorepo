<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Resolver;

use EonX\EasyLogging\Enum\BugsnagSeverity;
use Monolog\Level;

final class BugsnagSeverityResolver implements BugsnagSeverityResolverInterface
{
    public function resolve(Level $level): BugsnagSeverity
    {
        return match (true) {
            $level->value >= Level::Critical->value => BugsnagSeverity::Error,
            $level->value >= Level::Error->value => BugsnagSeverity::Warning,
            default => BugsnagSeverity::Info
        };
    }
}
