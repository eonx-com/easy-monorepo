<?php
declare(strict_types=1);

use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Controller\ResponseController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('response', '/response')
        ->controller(ResponseController::class);
};
