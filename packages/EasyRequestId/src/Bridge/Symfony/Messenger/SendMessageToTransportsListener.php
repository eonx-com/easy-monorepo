<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\Symfony\Messenger;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

final class SendMessageToTransportsListener
{
    /**
     * @var \EonX\EasyRequestId\Interfaces\RequestIdServiceInterface
     */
    private $requestIdService;

    public function __construct(RequestIdServiceInterface $requestIdService)
    {
        $this->requestIdService = $requestIdService;
    }

    public function __invoke(SendMessageToTransportsEvent $event): void
    {
        $event->setEnvelope($event->getEnvelope()->with(new RequestIdStamp(
            $this->requestIdService->getCorrelationId(),
            $this->requestIdService->getRequestId()
        )));
    }
}
