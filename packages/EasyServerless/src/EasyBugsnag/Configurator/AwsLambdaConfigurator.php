<?php
declare(strict_types=1);

namespace EonX\EasyServerless\EasyBugsnag\Configurator;

use Bugsnag\Client;
use EonX\EasyBugsnag\Common\Configurator\AbstractClientConfigurator;
use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;

final class AwsLambdaConfigurator extends AbstractClientConfigurator
{
    public function configure(Client $bugsnag): void
    {
        $bugsnag->setMetaData([
            'lambda' => [
                'invocation' => LambdaContextHelper::getInvocationContext(),
                'request' => LambdaContextHelper::getRequestContext(),
            ],
        ]);
    }
}
