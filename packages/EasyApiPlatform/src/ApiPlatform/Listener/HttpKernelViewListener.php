<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\ApiPlatform\Listener;

use ApiPlatform\Doctrine\Orm\Paginator;
use EonX\EasyApiPlatform\ApiPlatform\Paginator\CustomPaginator;
use Symfony\Component\HttpKernel\Event\ViewEvent;

final readonly class HttpKernelViewListener
{
    public function __invoke(ViewEvent $event): void
    {
        $controllerResult = $event->getControllerResult();

        if ($controllerResult instanceof Paginator) {
            $event->setControllerResult(new CustomPaginator($controllerResult));
        }
    }
}
