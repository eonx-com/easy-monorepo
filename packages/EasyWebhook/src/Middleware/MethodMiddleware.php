<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class MethodMiddleware extends AbstractConfigureOnceMiddleware
{
    /**
     * @var string
     */
    private $method;

    public function __construct(?string $method = null, ?int $priority = null)
    {
        $this->method = $method ?? WebhookInterface::DEFAULT_METHOD;

        parent::__construct($priority);
    }

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        if (empty($webhook->getMethod())) {
            $webhook->method($this->method);
        }

        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
