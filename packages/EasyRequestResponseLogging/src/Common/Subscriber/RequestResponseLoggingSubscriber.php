<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Common\Subscriber;

use EonX\EasyRequestResponseLogging\Common\Logger\RequestResponseLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final readonly class RequestResponseLoggingSubscriber implements EventSubscriberInterface
{
    private const DURATION_MS_ATTRIBUTE = '_easy_request_response_logging_duration_ms';

    private const STARTED_AT_ATTRIBUTE = '_easy_request_response_logging_started_at';

    public function __construct(
        private RequestResponseLogger $requestResponseLogger,
    ) {
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 1024],
            ResponseEvent::class => ['onKernelResponse', -1024],
            TerminateEvent::class => ['onKernelTerminate', -1024],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest() === false) {
            return;
        }

        $event->getRequest()
            ->attributes
            ->set(self::STARTED_AT_ATTRIBUTE, \microtime(true));
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->isMainRequest() === false) {
            return;
        }

        $attributes = $event->getRequest()
            ->attributes;
        $startedAt = $attributes->get(self::STARTED_AT_ATTRIBUTE);

        if (\is_float($startedAt)) {
            $attributes->set(self::DURATION_MS_ATTRIBUTE, \round((\microtime(true) - $startedAt) * 1000, 2));
        }
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $attributes = $event->getRequest()
            ->attributes;
        $durationMs = $attributes->get(self::DURATION_MS_ATTRIBUTE);
        $route = $attributes->get('_route');
        $routeParameters = $attributes->get('_route_params');

        $this->requestResponseLogger->log(
            request: $event->getRequest(),
            response: $event->getResponse(),
            durationMs: \is_float($durationMs) ? $durationMs : null,
            route: \is_string($route) ? $route : null,
            routeParameters: \is_string($route) && \is_array($routeParameters) ? $routeParameters : null
        );
    }
}
