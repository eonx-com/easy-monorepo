<?php

declare(strict_types=1);

namespace EonX\EasyWebhook;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyWebhook\Events\FailedWebhookEvent;
use EonX\EasyWebhook\Events\FinalFailedWebhookEvent;
use EonX\EasyWebhook\Events\SuccessWebhookEvent;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class WithEventsWebhookClient implements WebhookClientInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookClientInterface
     */
    private $decorated;

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(WebhookClientInterface $decorated, EventDispatcherInterface $dispatcher)
    {
        $this->decorated = $decorated;
        $this->dispatcher = $dispatcher;
    }

    public function configure(WebhookInterface $webhook): WebhookInterface
    {
        return $this->decorated->configure($webhook);
    }

    public function sendWebhook(WebhookInterface $webhook): WebhookResultInterface
    {
        $event = null;
        $result = $this->decorated->sendWebhook($webhook);

        switch ($result->getWebhook()->getStatus()) {
            case WebhookInterface::STATUS_FAILED:
                $event = new FinalFailedWebhookEvent($result);
                break;
            case WebhookInterface::STATUS_FAILED_PENDING_RETRY:
                $event = new FailedWebhookEvent($result);
                break;
            case WebhookInterface::STATUS_SUCCESS:
                $event = new SuccessWebhookEvent($result);
        }

        if ($event !== null) {
            $this->dispatcher->dispatch($event);
        }

        return $result;
    }
}
