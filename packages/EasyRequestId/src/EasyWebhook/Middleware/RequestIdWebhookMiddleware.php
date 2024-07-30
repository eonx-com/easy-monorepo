<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\EasyWebhook\Middleware;

use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Middleware\AbstractConfigureOnceMiddleware;
use EonX\EasyWebhook\Common\Stack\StackInterface;

final class RequestIdWebhookMiddleware extends AbstractConfigureOnceMiddleware
{
    public function __construct(
        private readonly RequestIdProviderInterface $requestIdProvider,
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
