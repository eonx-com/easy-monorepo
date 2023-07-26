<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyWebhook;

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\AbstractConfigureOnceMiddleware;

final class RequestIdWebhookMiddleware extends AbstractConfigureOnceMiddleware
{
    public function __construct(
        private RequestIdServiceInterface $requestIdService,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhook->header(
            $this->requestIdService->getCorrelationIdHeaderName(),
            $this->requestIdService->getCorrelationId()
        );
        $webhook->header($this->requestIdService->getRequestIdHeaderName(), $this->requestIdService->getRequestId());

        return $this->passOn($webhook, $stack);
    }
}
