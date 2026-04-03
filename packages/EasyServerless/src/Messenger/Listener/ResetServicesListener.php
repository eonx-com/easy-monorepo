<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Messenger\Listener;

use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

#[AsEventListener(priority: -1024)]
final readonly class ResetServicesListener
{
    public function __construct(
        private ServicesResetter $servicesResetter,
    ) {
    }

    public function __invoke(EnvelopeDispatchedEvent $event): void
    {
        $this->servicesResetter->reset();
    }
}
