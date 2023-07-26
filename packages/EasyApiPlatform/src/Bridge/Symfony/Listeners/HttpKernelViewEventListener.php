<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony\Listeners;

use ApiPlatform\Doctrine\Orm\Paginator;
use EonX\EasyApiPlatform\Paginators\CustomPaginator;
use Symfony\Component\HttpKernel\Event\ViewEvent;

final class HttpKernelViewEventListener
{
    public function __invoke(ViewEvent $event): void
    {
        $controllerResult = $event->getControllerResult();

        if ($controllerResult instanceof Paginator) {
            $event->setControllerResult(new CustomPaginator($controllerResult));
        }
    }
}
