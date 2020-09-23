<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyBugsnag;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasyRequestId\DeferredRequestIdServiceProvider;
use EonX\EasyRequestId\Interfaces\RequestIdKeysAwareInterface;
use EonX\EasyRequestId\Traits\RequestIdKeysAwareTrait;

final class RequestIdConfigurator extends AbstractClientConfigurator implements RequestIdKeysAwareInterface
{
    use RequestIdKeysAwareTrait;

    /**
     * @var \EonX\EasyRequestId\DeferredRequestIdServiceProvider
     */
    private $deferred;

    public function __construct(DeferredRequestIdServiceProvider $deferred, ?int $priority = null)
    {
        $this->deferred = $deferred;

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $service = $this->deferred->getRequestIdService();

        $bugsnag->setMetaData([
            'request' => [
                $this->getRequestIdKey() => $service->getRequestId(),
                $this->getCorrelationIdKey() => $service->getCorrelationId(),
            ],
        ]);
    }
}
