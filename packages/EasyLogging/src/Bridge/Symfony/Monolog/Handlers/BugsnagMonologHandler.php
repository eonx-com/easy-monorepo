<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\Monolog\Handlers;

use Bugsnag\Client;
use Bugsnag\Report;
use EonX\EasyLogging\Bridge\Symfony\Monolog\Resolvers\BugsnagSeverityResolverInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

final class BugsnagMonologHandler extends AbstractProcessingHandler
{
    /**
     * @inheritDoc
     */
    public function __construct(
        private BugsnagSeverityResolverInterface $bugsnagSeverityResolver,
        private Client $bugsnagClient,
        $level = null,
        ?bool $bubble = null,
    ) {
        parent::__construct($level ?? Logger::WARNING, $bubble ?? true);
    }

    protected function write(array $record): void
    {
        if (
            isset($record['context']['exception_reported_by_error_handler'])
            && $record['context']['exception_reported_by_error_handler'] === true
        ) {
            return;
        }

        $severity = $this->bugsnagSeverityResolver->resolve((int)$record['level']);
        $this->bugsnagClient
            ->notifyError(
                (string)$record['message'],
                (string)$record['formatted'],
                static function (Report $report) use ($record, $severity): void {
                    $report->setSeverity($severity);
                    $report->setMetaData(['context' => $record['context'], 'extra' => $record['extra']]);
                }
            );
    }
}
