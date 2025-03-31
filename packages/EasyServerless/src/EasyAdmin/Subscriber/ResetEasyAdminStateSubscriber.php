<?php
declare(strict_types=1);

namespace EonX\EasyServerless\EasyAdmin\Subscriber;

use Closure;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\EventListener\AdminRouterSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Twig\Environment;

final readonly class ResetEasyAdminStateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AdminRouterSubscriber $adminRouterSubscriber,
        private Environment $twig,
    ) {
    }

    /**
     * @return array<string, array<int, int|string>|string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', -100],
            TerminateEvent::class => 'onTerminate',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        // This is needed for EasyAdmin prior to pretty URLs feature
        // EasyAdmin sets the twig global "ea" as part of its extension,
        // because the stateful nature of swoole, this logic is executed only once,
        // this listener explicitly sets the admin context as twig global to prevent side effects
        $this->twig->addGlobal('ea', $event->getRequest()->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE));
    }

    public function onTerminate(TerminateEvent $event): void
    {
        // This is needed for EasyAdmin after pretty URLs feature
        if (\property_exists($this->adminRouterSubscriber, 'requestAlreadyProcessedAsPrettyUrl')) {
            Closure::bind(function (): void {
                $this->requestAlreadyProcessedAsPrettyUrl = false;
            }, $this->adminRouterSubscriber, AdminRouterSubscriber::class)();
        }
    }
}
