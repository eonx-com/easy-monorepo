<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\EasyWebhook\Middleware;

use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\AbstractConfigureOnceMiddleware;

final class RequestIdWebhookMiddleware extends AbstractConfigureOnceMiddleware
{
    public function __construct(
        private RequestIdInterface $requestId,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhook->header(
            $this->requestId->getCorrelationIdHeaderName(),
            $this->requestId->getCorrelationId()
        );
        $webhook->header($this->requestId->getRequestIdHeaderName(), $this->requestId->getRequestId());

        return $this->passOn($webhook, $stack);
    }
}
