<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;
use EonX\EasyServerless\Tests\Stub\SnapStart\Warmer\SnapStartWarmerStub;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_serverless', [
        'snap_start' => [
            'enabled' => true,
            // The warm-up only runs the Doctrine warmers, the RouteCollectionWarmer and the one below
            'warmers' => [
                'class_map' => [
                    'enabled' => false,
                ],
                'http_request' => [
                    'enabled' => false,
                ],
            ],
        ],
    ]);

    // Cannot be created as the environment variable is not set. With the default priority 0, it runs before the
    // ConnectionsWarmer
    $containerConfigurator->services()
        ->set('app.not_creatable_snap_start_warmer', SnapStartWarmerStub::class)
        ->arg('$name', env('EASY_SERVERLESS_TEST_NOT_SET_ENV_VAR'))
        ->tag(SnapStartTag::Warmer->value);
};
