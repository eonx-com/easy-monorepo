<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyBugsnag;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasyRequestId\Interfaces\RequestIdKeysAwareInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\Traits\RequestIdKeysAwareTrait;

final class RequestIdConfigurator extends AbstractClientConfigurator implements RequestIdKeysAwareInterface
{
    use RequestIdKeysAwareTrait;

    /**
     * @var \EonX\EasyRequestId\Interfaces\RequestIdServiceInterface
     */
    private $requestIdService;

    public function __construct(RequestIdServiceInterface $requestIdService, ?int $priority = null)
    {
        $this->requestIdService = $requestIdService;

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->getPipeline()
            ->pipe(new CallbackBridge(function (Report $report): void {
                $report->setMetaData([
                    'request' => [
                        $this->getRequestIdKey() => $this->requestIdService->getRequestId(),
                        $this->getCorrelationIdKey() => $this->requestIdService->getCorrelationId(),
                    ],
                ]);
            }));
    }
}
