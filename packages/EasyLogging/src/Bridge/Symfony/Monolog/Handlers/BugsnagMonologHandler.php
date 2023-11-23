<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\Monolog\Handlers;

use Bugsnag\Client;
use Bugsnag\Report;
use EonX\EasyLogging\Bridge\Symfony\Monolog\Resolvers\DefaultBugsnagSeverityResolverInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

final class BugsnagMonologHandler extends AbstractProcessingHandler
{
    use ServiceSubscriberTrait;

    /**
     * @inheritdoc
     */
    public function __construct(
        private DefaultBugsnagSeverityResolverInterface $bugsnagSeverityResolver,
        private Client $bugsnagClient,
        $level = null,
        ?bool $bubble = null,
    ) {
        parent::__construct($level ?? Logger::WARNING, $bubble ?? true);
    }

    protected function write(array $record): void
    {
        if (isset($record['context']['exception_handled_by_easy_error_handler'])) {
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
