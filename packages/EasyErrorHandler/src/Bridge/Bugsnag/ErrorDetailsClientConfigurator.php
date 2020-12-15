<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;

final class ErrorDetailsClientConfigurator extends AbstractClientConfigurator
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface
     */
    private $errorDetailsResolver;

    public function __construct(ErrorDetailsResolverInterface $errorDetailsResolver, ?int $priority = null)
    {
        $this->errorDetailsResolver = $errorDetailsResolver;

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                $throwable = $report->getOriginalError();

                if ($throwable instanceof \Throwable === false) {
                    return;
                }

                $report->addMetaData([
                    'error' => $this->errorDetailsResolver->resolveExtendedDetails($throwable),
                ]);
            }));
    }
}
