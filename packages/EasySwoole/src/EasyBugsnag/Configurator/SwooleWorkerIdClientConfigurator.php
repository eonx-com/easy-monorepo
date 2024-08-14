<?php
declare(strict_types=1);

namespace EonX\EasySwoole\EasyBugsnag\Configurator;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Common\Configurator\AbstractClientConfigurator;
use EonX\EasySwoole\Common\Enum\RequestAttribute;
use Symfony\Component\HttpFoundation\RequestStack;

final class SwooleWorkerIdClientConfigurator extends AbstractClientConfigurator
{
    public function __construct(
        private readonly RequestStack $requestStack,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                $workerId = $this->requestStack->getCurrentRequest()
                    ?->attributes->get(RequestAttribute::EasySwooleWorkerId->value);

                if (\is_int($workerId)) {
                    $report->addMetaData([
                        'swoole' => [
                            'workerId' => $workerId,
                        ],
                    ]);
                }
            }));
    }
}
