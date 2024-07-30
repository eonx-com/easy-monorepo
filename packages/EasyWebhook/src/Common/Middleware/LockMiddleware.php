<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyLock\Common\Locker\LockerInterface;
use EonX\EasyLock\Common\ValueObject\LockData;
use EonX\EasyLock\Common\ValueObject\LockDataInterface;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

final class LockMiddleware extends AbstractMiddleware
{
    private const DEFAULT_LOCK_RESOURCE_PATTERN = 'easy_webhook_send_%s';

    private readonly string $resourcePattern;

    public function __construct(
        private readonly LockerInterface $locker,
        ?string $lockResourcePattern = null,
        ?int $priority = null,
    ) {
        $this->resourcePattern = $lockResourcePattern ?? self::DEFAULT_LOCK_RESOURCE_PATTERN;

        parent::__construct($priority);
    }

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface
    {
        $func = fn (): WebhookResultInterface => $this->passOn($webhook, $stack);

        $result = $webhook->getId() !== null && $webhook->isSendNow()
            ? $this->locker->processWithLock($this->getLockData($webhook), $func)
            : $func();

        return $result instanceof WebhookResultInterface ? $result : new WebhookResult($webhook);
    }

    private function getLockData(WebhookInterface $webhook): LockDataInterface
    {
        return LockData::create(\sprintf($this->resourcePattern, $webhook->getId()));
    }
}
