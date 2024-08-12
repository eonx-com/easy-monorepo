<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bugsnag\Configurator;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Common\Configurator\AbstractClientConfigurator;
use EonX\EasyErrorHandler\Common\Enum\ExceptionSeverity;
use EonX\EasyErrorHandler\Common\Exception\SeverityAwareExceptionInterface;
use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;
use Monolog\Level;
use Throwable;

final class SeverityClientConfigurator extends AbstractClientConfigurator
{
    private const MAPPING = [
        Level::Error->value => ExceptionSeverity::Error,
        Level::Info->value => ExceptionSeverity::Info,
        Level::Warning->value => ExceptionSeverity::Warning,
    ];

    public function __construct(
        private readonly ErrorLogLevelResolverInterface $errorLogLevelResolver,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                $throwable = $report->getOriginalError();

                if ($throwable instanceof Throwable) {
                    $report->setSeverity($this->getSeverity($throwable)?->value);
                }
            }));
    }

    private function getSeverity(Throwable $throwable): ?ExceptionSeverity
    {
        // Allow to explicitly define the severity
        $severity = $throwable instanceof SeverityAwareExceptionInterface ? $throwable->getSeverity() : null;

        if ($severity !== null) {
            return $severity;
        }

        $logLevel = $this->errorLogLevelResolver->getLogLevel($throwable);

        return self::MAPPING[$logLevel->value] ?? null;
    }
}
