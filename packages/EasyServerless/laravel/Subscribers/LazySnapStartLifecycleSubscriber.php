<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\Subscribers;

use Bref\Context\Context;
use Bref\Event\Handler;
use Bref\Listener\BrefEventSubscriber;
use EonX\EasyServerless\Laravel\Warmers\DatabaseConnectionsWarmer;
use EonX\EasyServerless\SnapStart\Subscriber\SnapStartLifecycleSubscriber;
use Illuminate\Contracts\Container\Container;
use Laravel\Lumen\Application as LumenApplication;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Subscribed when the service provider is registered, it creates the SnapStartLifecycleSubscriber on afterStartup:
 * the warmers and the after restore hooks tagged by the providers registered or booted later are then known.
 *
 * Like the SnapStartLifecycleSubscriber, it never throws: Bref does not catch the exceptions of afterStartup, an
 * exception would fail the initialization.
 */
final class LazySnapStartLifecycleSubscriber extends BrefEventSubscriber
{
    private ?SnapStartLifecycleSubscriber $snapStartLifecycleSubscriber = null;

    public function __construct(
        private readonly Container $container,
    ) {
    }

    public function afterStartup(): void
    {
        // Lumen boots the providers on the first request, after the snapshot. Booted now, its boot is in the snapshot,
        // and the warmers run against a booted application
        if ($this->container instanceof LumenApplication) {
            try {
                $this->container->boot();
            } catch (Throwable $throwable) {
                $this->logError('SnapStart could not boot the Lumen application', $throwable);
            }
        }

        try {
            /** @var \EonX\EasyServerless\SnapStart\Subscriber\SnapStartLifecycleSubscriber $snapStartLifecycleSubscriber */
            $snapStartLifecycleSubscriber = $this->container->make(SnapStartLifecycleSubscriber::class);
        } catch (Throwable $throwable) {
            $this->logError('SnapStart lifecycle subscriber could not be created', $throwable);

            // It still closes the database connections, e.g. opened by the Lumen boot, and reseeds mt_rand() after the
            // restore
            $snapStartLifecycleSubscriber = new SnapStartLifecycleSubscriber(
                [
                    DatabaseConnectionsWarmer::class => fn (): DatabaseConnectionsWarmer
                        => new DatabaseConnectionsWarmer($this->container),
                ],
                []
            );
        }

        $this->snapStartLifecycleSubscriber = $snapStartLifecycleSubscriber;
        $this->snapStartLifecycleSubscriber->afterStartup();
    }

    public function beforeInvoke(
        callable|Handler|RequestHandlerInterface $handler,
        mixed $event,
        Context $context,
    ): void {
        $this->snapStartLifecycleSubscriber?->beforeInvoke($handler, $event, $context);
    }

    private function logError(string $message, Throwable $throwable): void
    {
        try {
            /** @var \Psr\Log\LoggerInterface $logger */
            $logger = $this->container->make(LoggerInterface::class);
            $logger->error($message, [
                'exception' => $throwable,
            ]);
        } catch (Throwable) {
            \error_log(\sprintf('%s: %s', $message, $throwable));
        }
    }
}
