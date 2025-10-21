<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\Loader;

use EonX\EasyServerless\Bundle\Enum\BundleParam;
use EonX\EasyServerless\Health\Controller\HealthCheckController;
use RuntimeException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class HealthCheckRouteLoader extends Loader
{
    private bool $isLoaded = false;

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        if ($this->isLoaded) {
            throw new RuntimeException(\sprintf(
                'Do not add the "%s" loader twice',
                BundleParam::RouteType->value
            ));
        }

        $routes = new RouteCollection();
        $routes->add('easy_serverless.health_check', new Route(
            path: '/easy-serverless/health-check',
            defaults: ['_controller' => HealthCheckController::class],
            host: null,
            methods: [Request::METHOD_GET]
        ));

        $this->isLoaded = true;

        return $routes;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $type === BundleParam::RouteType->value;
    }
}
