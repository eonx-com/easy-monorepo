<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\Listener;

use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\SecurityHeader\Hydrator\SecurityHeadersHydrator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener(event: ResponseEvent::class, priority: -10_000)]
final readonly class SecurityHeadersResponseListener
{
    public function __construct(
        private SecurityHeadersHydrator $securityHeaderHydrator,
    ) {}

    public function __invoke(ResponseEvent $event): void
    {
        if (LambdaContextHelper::inLambda() === false) {
            return;
        }

        $event->setResponse(
            $this->securityHeaderHydrator->hydrateResponse(
                $event->getResponse()
            )
        );
    }
}
