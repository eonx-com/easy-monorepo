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
     * @var string[]
     */
    private const MAPPING = [
        Logger::INFO => SeverityAwareExceptionInterface::SEVERITY_INFO,
        Logger::WARNING => SeverityAwareExceptionInterface::SEVERITY_WARNING,
        Logger::ERROR => SeverityAwareExceptionInterface::SEVERITY_ERROR,
    ];

    /**
     * @var null|int
     */
    private $threshold;

    public function __construct(?int $threshold = null, ?int $priority = null)
    {
        if ($threshold !== null) {
            @\trigger_error(
                'Passing $threshold is deprecated since 3.0 and will be removed in 4.0.',
                \E_USER_DEPRECATED
            );

            $this->threshold = $threshold;
        }

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

        if ($this->threshold !== null && $logLevel > $this->threshold) {
            // If log level greater than threshold, severity to error
            return SeverityAwareExceptionInterface::SEVERITY_ERROR;
        }

        return self::MAPPING[$logLevel] ?? null;
    }
}
