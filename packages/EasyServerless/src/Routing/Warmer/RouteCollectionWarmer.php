<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Routing\Warmer;

use EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * The compiled router never runs the route loaders, but the route collection does. Some loaders fill caches on the way,
 * e.g. the EasyAdmin loader fills the "cache.easyadmin" maps: without them, AdminControllerRegistry keeps empty maps
 * for the life of the process if a plain admin route reads it before any EasyAdmin route.
 */
final readonly class RouteCollectionWarmer implements SnapStartWarmerInterface
{
    public function __construct(
        private ?RouterInterface $router = null,
    ) {
    }

    public function warm(): void
    {
        $this->router?->getRouteCollection();
    }
}
