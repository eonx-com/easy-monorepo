<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\EasyAdmin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

final class AdminContextAsTwigGlobalListener
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        // EasyAdmin sets the twig global "ea" as part of its extension,
        // because the stateful nature of swoole, this logic is executed only once,
        // this listener explicitly sets the admin context as twig global to prevent side effects
        $this->twig->addGlobal('ea', $event->getRequest()->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE));
    }
}
