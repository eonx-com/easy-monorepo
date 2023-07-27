<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SeverityAwareExceptionInterface;
use Monolog\Logger;
use Throwable;

final class SeverityClientConfigurator extends AbstractClientConfigurator
{
    private const MAPPING = [
        Logger::ERROR => SeverityAwareExceptionInterface::SEVERITY_ERROR,
        Logger::INFO => SeverityAwareExceptionInterface::SEVERITY_INFO,
        Logger::WARNING => SeverityAwareExceptionInterface::SEVERITY_WARNING,
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
                    $report->setSeverity($this->getSeverity($throwable));
                }
            }));
    }

    private function getSeverity(Throwable $throwable): ?string
    {
        // Allow to explicitly define the severity
        $severity = $throwable instanceof SeverityAwareExceptionInterface ? $throwable->getSeverity() : null;

        if ($severity !== null) {
            return $severity;
        }

        $logLevel = $this->errorLogLevelResolver->getLogLevel($throwable);

        return self::MAPPING[$logLevel] ?? null;
    }
}
