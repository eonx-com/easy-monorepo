<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyWebhook;

use EonX\EasyRequestId\Interfaces\RequestIdKeysAwareInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\Traits\RequestIdKeysAwareTrait;
use EonX\EasyWebhook\Configurators\AbstractWebhookConfigurator;
use EonX\EasyWebhook\Interfaces\WebhookInterface;

final class RequestIdWebhookConfigurator extends AbstractWebhookConfigurator implements RequestIdKeysAwareInterface
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

    public function configure(WebhookInterface $webhook): void
    {
        $webhook->mergeHttpClientOptions([
            'headers' => [
                $this->getCorrelationIdKey() => $this->requestIdService->getCorrelationId(),
            ],
        ]);
    }
}
