<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Tests\Fixture\App\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(priority: 2048)]
final class EarlyResponseListener
{
    public const HEADER = 'X-Early-Response';

    public function __invoke(RequestEvent $event): void
    {
        if ($event->getRequest()->headers->has(self::HEADER)) {
            $event->setResponse(new Response());
        }
    }
}
