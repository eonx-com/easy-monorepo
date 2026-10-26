<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Datadog\Listener;

use EonX\EasyServerless\Datadog\Helper\RootSpanHelper;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * Adds PHP runtime information to the Datadog root span of incoming requests, messages and console commands.
 * Messages are covered both when consumed by a worker and when handled by the serverless SqsHandler,
 * as both dispatch WorkerMessageReceivedEvent.
 */
#[AsEventListener(event: ConsoleCommandEvent::class, method: 'onConsoleCommand')]
#[AsEventListener(event: RequestEvent::class, method: 'onRequest', priority: 10_000)]
#[AsEventListener(event: WorkerMessageReceivedEvent::class, method: 'onWorkerMessageReceived')]
final class PhpRuntimeInfoListener
{
    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        RootSpanHelper::addPhpRuntimeInfo();
    }

    public function onRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            RootSpanHelper::addPhpRuntimeInfo();
        }
    }

    public function onWorkerMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        RootSpanHelper::addPhpRuntimeInfo();
    }
}
