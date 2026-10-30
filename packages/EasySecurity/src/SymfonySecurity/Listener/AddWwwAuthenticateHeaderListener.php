<?php
declare(strict_types=1);

namespace EonX\EasySecurity\SymfonySecurity\Listener;

use EonX\EasySecurity\SymfonySecurity\Resolver\WwwAuthenticateHeaderResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final readonly class AddWwwAuthenticateHeaderListener
{
    private const HEADER_WWW_AUTHENTICATE = 'WWW-Authenticate';

    public function __construct(
        private WwwAuthenticateHeaderResolver $wwwAuthenticateHeaderResolver,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        if (
            $event->isMainRequest() === false
            || $response->getStatusCode() !== Response::HTTP_UNAUTHORIZED
            || $response->headers->has(self::HEADER_WWW_AUTHENTICATE)
        ) {
            return;
        }

        $response->headers->set(
            self::HEADER_WWW_AUTHENTICATE,
            $this->wwwAuthenticateHeaderResolver->resolve($event->getRequest())
        );
    }
}
