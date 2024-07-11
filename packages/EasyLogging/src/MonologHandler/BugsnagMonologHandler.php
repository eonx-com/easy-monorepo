<?php
declare(strict_types=1);

namespace EonX\EasyLogging\MonologHandler;

use Bugsnag\Client;
use Bugsnag\Report;
use EonX\EasyLogging\Resolver\BugsnagSeverityResolverInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

final class BugsnagMonologHandler extends AbstractProcessingHandler
{
    /**
     * @inheritdoc
     */
    public function __construct(
        private BugsnagSeverityResolverInterface $bugsnagSeverityResolver,
        private Client $bugsnagClient,
        int|string|null|Level $level = null,
        ?bool $bubble = null,
    ) {
        parent::__construct($level ?? Level::Warning, $bubble ?? true);
    }

    protected function write(LogRecord $record): void
    {
        if (
            isset($record->context['exception_reported_by_error_handler'])
            && $record->context['exception_reported_by_error_handler'] === true
        ) {
            return;
        }

        $severity = $this->bugsnagSeverityResolver->resolve($record->level);
        $this->bugsnagClient
            ->notifyError(
                $record->message,
                (string)$record->formatted,
                static function (Report $report) use ($record, $severity): void {
                    $report->setSeverity($severity->value);
                    $report->setMetaData(['context' => $record->context, 'extra' => $record->extra]);
                }
            );
    }
}
