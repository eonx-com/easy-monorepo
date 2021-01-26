<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SeverityAwareExceptionInterface;
use EonX\EasyErrorHandler\Traits\DealsWithErrorLogLevelTrait;
use Monolog\Logger;

final class SeverityClientConfigurator extends AbstractClientConfigurator
{
    use DealsWithErrorLogLevelTrait;

    /**
     * @var int
     */
    private $threshold;

    public function __construct(?int $threshold = null, ?int $priority = null)
    {
        $this->threshold = $threshold ?? Logger::ERROR;

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                $throwable = $report->getOriginalError();

                if ($throwable instanceof \Throwable) {
                    $report->setSeverity($this->getSeverity($throwable));
                }
            }));
    }

    private function getSeverity(\Throwable $throwable): ?string
    {
        // Allow to explicitly define the severity
        $severity = $throwable instanceof SeverityAwareExceptionInterface ? $throwable->getSeverity() : null;

        if ($severity !== null) {
            return $severity;
        }

        $logLevel = $this->getLogLevel($throwable);

        // If log level greater than threshold, severity to error, otherwise default to null
        return $logLevel > $this->threshold ? SeverityAwareExceptionInterface::SEVERITY_ERROR : null;
    }
}
