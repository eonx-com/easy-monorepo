<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SeverityAwareExceptionInterface;
use Monolog\Logger;

final class SeverityClientConfigurator extends AbstractClientConfigurator
{
    /**
     * @var string[]
     */
    private const MAPPING = [
        Logger::INFO => SeverityAwareExceptionInterface::SEVERITY_INFO,
        Logger::WARNING => SeverityAwareExceptionInterface::SEVERITY_WARNING,
        Logger::ERROR => SeverityAwareExceptionInterface::SEVERITY_ERROR,
    ];

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorLogLevelResolverInterface
     */
    private $errorLogLevelResolver;

    /**
     * @var null|int
     */
    private $threshold;

    public function __construct(
        ErrorLogLevelResolverInterface $errorLogLevelResolver,
        ?int $threshold = null,
        ?int $priority = null
    ) {
        $this->errorLogLevelResolver = $errorLogLevelResolver;

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

        $logLevel = $this->errorLogLevelResolver->getLogLevel($throwable);

        if ($this->threshold !== null && $logLevel > $this->threshold) {
            // If log level greater than threshold, severity to error
            return SeverityAwareExceptionInterface::SEVERITY_ERROR;
        }

        return self::MAPPING[$logLevel] ?? null;
    }
}
