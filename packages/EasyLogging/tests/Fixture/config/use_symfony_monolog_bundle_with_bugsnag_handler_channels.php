<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bugsnag\Client;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_logging', [
        'use_symfony_monolog_bundle' => true,
        'bugsnag_handler' => true,
        'bugsnag_handler_channels' => ['app'],
    ]);

    $containerConfigurator->extension('monolog', [
        'channels' => ['other'],
        'handlers' => [
            'main' => [
                'type' => 'test',
            ],
        ],
    ]);

    $containerConfigurator->services()
        ->set(Client::class)
        ->factory([Client::class, 'make'])
        ->args(['00000000000000000000000000000000']);
};
