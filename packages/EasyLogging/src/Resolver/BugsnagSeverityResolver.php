<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Resolver;

use Monolog\Logger;

final class BugsnagSeverityResolver implements BugsnagSeverityResolverInterface
{
    public function resolve(int $level): string
    {
        return match (true) {
            $level >= Logger::CRITICAL => self::SEVERITY_ERROR,
            $level >= Logger::ERROR => self::SEVERITY_WARNING,
            default => self::SEVERITY_INFO
        };
    }
}
