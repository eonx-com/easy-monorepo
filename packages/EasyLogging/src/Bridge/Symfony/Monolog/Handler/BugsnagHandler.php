<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\Monolog\Handler;

use Bugsnag\Client;
use Bugsnag\Report;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SeverityAwareExceptionInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

final class BugsnagHandler extends AbstractProcessingHandler implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    /**
     * @inheritdoc
     */
    public function __construct($level = null, ?bool $bubble = null)
    {
        parent::__construct($level ?? Logger::WARNING, $bubble ?? true);
    }

    #[SubscribedService(key: Client::class)]
    public function getBugsnagClient(): Client
    {
        return $this->container->get(Client::class);
    }

    protected function write(array $record): void
    {
        $this->getBugsnagClient()->notifyError(
            (string)$record['message'],
            (string)$record['formatted'],
            static function (Report $report) use ($record): void {
                $report->setSeverity(self::mapMonologSeverityToBugsnagSeverity((int)$record['level']));
                $report->setMetaData(['context' => $record['context'], 'extra' => $record['extra']]);
            }
        );
    }

    private static function mapMonologSeverityToBugsnagSeverity(int $level): string
    {
        return match (true) {
            $level >= Logger::CRITICAL => SeverityAwareExceptionInterface::SEVERITY_ERROR,
            $level >= Logger::ERROR => SeverityAwareExceptionInterface::SEVERITY_WARNING,
            default => SeverityAwareExceptionInterface::SEVERITY_INFO
        };
    }
}
