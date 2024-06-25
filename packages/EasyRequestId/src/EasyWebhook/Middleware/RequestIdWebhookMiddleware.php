<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\EasyWebhook\Middleware;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\AbstractConfigureOnceMiddleware;

final class RequestIdWebhookMiddleware extends AbstractConfigureOnceMiddleware
{
    public function __construct(
        private RequestIdProviderInterface $requestIdProvider,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhook->header(
            $this->requestIdProvider->getCorrelationIdHeaderName(),
            $this->requestIdProvider->getCorrelationId()
        );
        $webhook->header($this->requestIdProvider->getRequestIdHeaderName(), $this->requestIdProvider->getRequestId());

        return $this->passOn($webhook, $stack);
    }
}
