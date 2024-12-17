<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Common\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener]
final class OutputSanitizerListener
{
    public function __invoke(ResponseEvent $event): void
    {
        if ($event->isMainRequest() === false) {
            return;
        }

        $response = $event->getResponse();

        if (\str_contains((string)$response->headers->get('Content-Type'), 'application/json') === false) {
            return;
        }

        $content = $response->getContent();

        if ($content === false) {
            return;
        }

        $response->setContent(\htmlspecialchars($content));
    }
}
