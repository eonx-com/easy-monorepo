<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('default', '/')
        ->controller('Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction')
        ->defaults(['route' => 'api_doc']);

    $routingConfigurator->import('.', 'api_platform');
};
