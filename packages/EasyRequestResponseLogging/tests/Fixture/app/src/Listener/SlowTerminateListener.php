<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Fixture\App\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

#[AsEventListener]
final class SlowTerminateListener
{
    public const HEADER = 'X-Slow-Terminate';

    public function __invoke(TerminateEvent $event): void
    {
        if ($event->getRequest()->headers->has(self::HEADER)) {
            \usleep(300000);
        }
    }
}
