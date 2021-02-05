<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge\EasyWebhook;

use EonX\EasyRequestId\Interfaces\RequestIdKeysAwareInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\Traits\RequestIdKeysAwareTrait;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\AbstractConfigureOnceMiddleware;

final class RequestIdWebhookMiddleware extends AbstractConfigureOnceMiddleware implements RequestIdKeysAwareInterface
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

    protected function doProcess(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $webhook->mergeHttpClientOptions([
            'headers' => [
                $this->getCorrelationIdKey() => $this->requestIdService->getCorrelationId(),
            ],
        ]);

        return $stack
            ->next()
            ->process($webhook, $stack);
    }
}
