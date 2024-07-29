<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

final class MethodMiddleware extends AbstractConfigureOnceMiddleware
{
    private readonly string $method;

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
