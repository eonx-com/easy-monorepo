<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bugsnag;

use Bugsnag\Report;
use EonX\EasyLogging\Interfaces\ExternalLogClientInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Throwable;

/**
 * @deprecated since 2.4, will be removed in 3.0. Bugsnag implementation will be reworked.
 */
final class BugsnagHandler extends AbstractProcessingHandler
{
    /**
     * @var \EonX\EasyLogging\Interfaces\ExternalLogClientInterface
     */
    private $client;

    /**
     * @var string[] The list of exceptions not to report to bugsnag
     */
    private $doNotReport = [];

    public function __construct(ExternalLogClientInterface $client, ?int $level = null, ?bool $bubble = null)
    {
        parent::__construct($level ?? Logger::ERROR, $bubble ?? true);

        $this->client = $client;
    }

    /**
     * @param string[] $doNoReport
     */
    public function setDoNotReport(array $doNoReport): self
    {
        $this->doNotReport = $doNoReport;

        return $this;
    }

    /**
     * @param mixed[] $record
     */
    protected function write(array $record): void
    {
        // Notify exception if context exception exists
        $exception = $record['context']['exception'] ?? null;

        if (($exception instanceof Throwable) === true) {
            // Do not combine if statements so we keep the notifyError feature if no exception in context
            if ($this->shouldReport($exception)) {
                $this->client->notifyException($exception, $this->getNotifyCallback($record));
            }

            return;
        }

        $this->client->notifyError(
            $record['message'] ?? 'Error Notification',
            $record['formatted'] ?? '',
            $this->getNotifyCallback($record)
        );
    }

    /**
     * @param mixed[] $record
     */
    private function getNotifyCallback(array $record): \Closure
    {
        return static function (Report $report) use ($record): void {
            $report->setSeverity(LogLevel::ERROR);

            $extra = $record['extra'] ?? null;

            if ($extra !== null) {
                $report->setMetaData($extra);
            }
        };
    }

    private function shouldReport(\Throwable $throwable): bool
    {
        foreach ($this->doNotReport as $type) {
            if (\is_a($throwable, $type)) {
                return false;
            }
        }

        return true;
    }
}
