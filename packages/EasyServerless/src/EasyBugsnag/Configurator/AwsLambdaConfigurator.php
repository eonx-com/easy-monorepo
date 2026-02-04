<?php
declare(strict_types=1);

namespace EonX\EasyServerless\EasyBugsnag\Configurator;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Common\Configurator\AbstractClientConfigurator;
use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;

final class AwsLambdaConfigurator extends AbstractClientConfigurator
{
    public function configure(Client $bugsnag): void
    {
        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                $report->addMetaData([
                    'lambda' => [
                        'handler' => LambdaContextHelper::getHandler(),
                        'invocation' => LambdaContextHelper::getInvocationContext(),
                        'request' => LambdaContextHelper::getRequestContext(),
                        'taskRoot' => LambdaContextHelper::getTaskRoot(),
                    ],
                ]);
            }));
    }
}
