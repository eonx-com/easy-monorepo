<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\Monolog\Handlers;

use Bugsnag\Client;
use Bugsnag\Report;
use EonX\EasyLogging\Bridge\Symfony\Monolog\Resolvers\DefaultBugsnagSeverityResolverInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

final class BugsnagMonologHandler extends AbstractProcessingHandler implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    /**
     * @inheritdoc
     */
    public function __construct(
        private readonly DefaultBugsnagSeverityResolverInterface $bugsnagSeverityResolver,
        $level = null,
        ?bool $bubble = null,
    ) {
        parent::__construct($level ?? Logger::WARNING, $bubble ?? true);
    }

    #[SubscribedService(key: Client::class)]
    public function getBugsnagClient(): Client
    {
        return $this->container->get(Client::class);
    }

    protected function write(array $record): void
    {
        $severity = $this->bugsnagSeverityResolver->resolve((int)$record['level']);
        $this->getBugsnagClient()
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
