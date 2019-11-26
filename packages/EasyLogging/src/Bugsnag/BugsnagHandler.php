<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bugsnag;

use EonX\EasyLogging\Interfaces\ExternalLogClientInterface;
use Bugsnag\Report;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;

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

    /**
     * BugsnagHandler constructor.
     *
     * @param \EonX\EasyLogging\Interfaces\ExternalLogClientInterface $client
     * @param null|int $level
     * @param null|bool $bubble
     */
    public function __construct(ExternalLogClientInterface $client, ?int $level = null, ?bool $bubble = null)
    {
        parent::__construct($level ?? Logger::ERROR, $bubble ?? true);

        $this->client = $client;
    }

    /**
     * Set list of exceptions not to report to bugsnag.
     *
     * @param string[] $doNoReport
     *
     * @return $this
     */
    public function setDoNotReport(array $doNoReport): self
    {
        $this->doNotReport = $doNoReport;

        return $this;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param mixed[] $record
     *
     * @return void
     */
    protected function write(array $record): void
    {
        // Notify exception if context exception exists
        $exception = $record['context']['exception'] ?? null;

        if ($exception !== null) {
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
     * Get common notify callback.
     *
     * @param mixed[] $record
     *
     * @return \Closure
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

    /**
     * Check if given throwable should be reported to bugsnag.
     *
     * @param \Throwable $throwable
     *
     * @return bool
     */
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
