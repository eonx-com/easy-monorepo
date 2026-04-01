<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Messenger\Listener;

use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

final readonly class ResetServicesListener implements EventSubscriberInterface
{
    public function __construct(
        private ServicesResetter $servicesResetter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EnvelopeDispatchedEvent::class => ['resetServices', -1024],
        ];
    }

    public function resetServices(EnvelopeDispatchedEvent $event): void
    {
        $this->servicesResetter->reset();
    }
}
