<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bugsnag\Configurator;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolverInterface;
use Throwable;

final class ErrorDetailsClientConfigurator extends AbstractClientConfigurator
{
    public function __construct(
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                $throwable = $report->getOriginalError();

                if ($throwable instanceof Throwable === false) {
                    return;
                }

                $report->setMessage($this->errorDetailsResolver->resolveInternalMessage($throwable));
                $report->addMetaData([
                    'error' => $this->errorDetailsResolver->resolveExtendedDetails($throwable),
                ]);
            }));
    }
}
