<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\EasyBugsnag;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasySwoole\Interfaces\RequestAttributesInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class SwooleWorkerIdConfigurator extends AbstractClientConfigurator
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
                    ?->attributes->get(RequestAttributesInterface::EASY_SWOOLE_WORKER_ID);

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
