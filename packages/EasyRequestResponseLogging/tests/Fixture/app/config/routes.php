<?php
declare(strict_types=1);

use EonX\EasyRequestResponseLogging\Tests\Fixture\App\Controller\ResponseController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('lookup', '/lookup/{value}')
        ->controller(ResponseController::class);

    $routes->add('response', '/response')
        ->controller(ResponseController::class);

    $routes->add('token', '/token/{token}')
        ->controller(ResponseController::class)
        ->requirements([
            'token' => '.+',
        ]);
};
