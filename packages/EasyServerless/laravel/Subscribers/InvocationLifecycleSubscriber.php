<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\Subscribers;

use Bref\Context\Context;
use Bref\Event\Handler;
use Bref\Listener\BrefEventSubscriber;
use EonX\EasyServerless\Laravel\HttpHandlers\OctaneHttpHandler;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class InvocationLifecycleSubscriber extends BrefEventSubscriber
{
    public function afterInvoke(
        callable|Handler|RequestHandlerInterface $handler,
        mixed $event,
        Context $context,
        mixed $result,
        ?Throwable $error = null,
    ): void {
        if ($handler instanceof OctaneHttpHandler) {
            $handler->afterInvoke($context);
        }
    }
}
