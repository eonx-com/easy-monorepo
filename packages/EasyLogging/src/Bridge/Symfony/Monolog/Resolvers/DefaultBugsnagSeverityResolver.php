<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\Monolog\Resolvers;

use Monolog\Logger;

final class DefaultBugsnagSeverityResolver implements DefaultBugsnagSeverityResolverInterface
{
    public function resolve(int $level): string
    {
        return match (true) {
            $level >= Logger::ERROR => self::SEVERITY_ERROR,
            default => self::SEVERITY_WARNING
        };
    }
}
