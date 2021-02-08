<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Middleware;

use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockData;
use EonX\EasyWebhook\Interfaces\StackInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\WebhookResult;

final class LockMiddleware extends AbstractMiddleware
{
    /**
     * @var \EonX\EasyLock\Interfaces\LockServiceInterface
     */
    private $lockService;

    public function __construct(LockServiceInterface $lockService, ?int $priority = null)
    {
        $this->lockService = $lockService;

        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $data = LockData::create(\sprintf('easy_webhook_send_%s', $webhook->getId() ?? \spl_object_hash($webhook)));

        $func = static function () use ($webhook, $stack): WebhookResultInterface {
            return $stack
                ->next()
                ->process($webhook, $stack);
        };

        $result = $this->lockService->processWithLock($data, $func);

        return $result instanceof WebhookResultInterface ? $result : new WebhookResult($webhook);
    }
}
