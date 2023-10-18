<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Monolog\Report;

use Bugsnag\Configuration;
use Bugsnag\Report;
use Bugsnag\Stacktrace;

final class MonologReport extends Report
{
    public static function fromMonologRecord(Configuration $config, array $record, string $severity): self
    {
        $report = new self($config);

        if (isset($record['context']['exception'])) {
            $exceptionInfo = $record['context']['exception'];
            $report
                ->setName($exceptionInfo['class'])
                ->setStacktrace(
                    Stacktrace::fromBacktrace(
                        $config,
                        $exceptionInfo['trace'],
                        $exceptionInfo['file'],
                        $exceptionInfo['line']
                    )
                );
        }

        if (isset($record['context']['exception']) === false) {
            $report->setName($record['message'])->setStacktrace(Stacktrace::generate($config));
        }

        $report
            ->setMessage($record['formatted'])
            ->setSeverity($severity)
            ->setMetaData(['context' => $record['context'], 'extra' => $record['extra']]);

        return $report;
    }
}
