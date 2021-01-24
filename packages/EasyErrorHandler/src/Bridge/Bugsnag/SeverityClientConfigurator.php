<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SeverityAwareExceptionInterface;

final class SeverityClientConfigurator extends AbstractClientConfigurator
{
    public function configure(Client $bugsnag): void
    {
        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                $throwable = $report->getOriginalError();

                $report->setSeverity(
                    $throwable instanceof SeverityAwareExceptionInterface ? $throwable->getSeverity() : null
                );
            }));
    }
}
