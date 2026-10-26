<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Fixture\App\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

#[AsEventListener(priority: -2048)]
final class LastTerminateListener
{
    public const ATTRIBUTE = '_last_terminate_listener_called';

    public function __invoke(TerminateEvent $event): void
    {
        $event->getRequest()
            ->attributes
            ->set(self::ATTRIBUTE, true);
    }
}
