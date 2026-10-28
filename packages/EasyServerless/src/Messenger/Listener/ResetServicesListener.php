<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Messenger\Listener;

use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Service\ResetInterface;

#[AsEventListener(priority: -1024)]
final readonly class ResetServicesListener
{
    public function __construct(
        private ResetInterface $servicesResetter,
    ) {}

    public function __invoke(EnvelopeDispatchedEvent $event): void
    {
        $this->servicesResetter->reset();
    }
}
