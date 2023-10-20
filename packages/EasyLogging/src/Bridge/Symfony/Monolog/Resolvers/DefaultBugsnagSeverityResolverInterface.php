<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\Monolog\Resolvers;

interface DefaultBugsnagSeverityResolverInterface
{
    public const SEVERITY_ERROR = 'error';

    public const SEVERITY_INFO = 'info';

    public const SEVERITY_WARNING = 'warning';

    public function resolve(int $level): string;
}
