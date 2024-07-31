<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Resolver;

use EonX\EasyLogging\Enum\BugsnagSeverity;
use Monolog\Logger;

final class BugsnagSeverityResolver implements BugsnagSeverityResolverInterface
{
    public function resolve(int $level): BugsnagSeverity
    {
        return match (true) {
            $level >= Logger::CRITICAL => BugsnagSeverity::Error,
            $level >= Logger::ERROR => BugsnagSeverity::Warning,
            default => BugsnagSeverity::Info
        };
    }
}
