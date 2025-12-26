<?php
declare(strict_types=1);

namespace EonX\EasyServerless\EasyAdmin\Subscriber;

use Closure;
use EasyCorp\Bundle\EasyAdminBundle\EventListener\AdminRouterSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final readonly class ResetEasyAdminStateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AdminRouterSubscriber $adminRouterSubscriber,
    ) {
    }

    /**
     * @return array<string, array<int, int|string>|string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            TerminateEvent::class => 'onTerminate',
        ];
    }

    public function onTerminate(TerminateEvent $event): void
    {
        // This is needed for EasyAdmin after pretty URLs feature
        Closure::bind(
            function (): void {
                $this->requestAlreadyProcessedAsPrettyUrl = false;
            },
            $this->adminRouterSubscriber,
            AdminRouterSubscriber::class
        )();
    }
}
