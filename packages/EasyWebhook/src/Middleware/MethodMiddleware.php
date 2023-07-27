<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class MethodMiddleware extends AbstractConfigureOnceMiddleware
{
    private string $method;

    public function __construct(?string $method = null, ?int $priority = null)
    {
        $this->method = $method ?? WebhookInterface::DEFAULT_METHOD;

        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhook->method($webhook->getMethod() ?? $this->method);

        return $this->passOn($webhook, $stack);
    }
}
