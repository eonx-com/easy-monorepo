<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Loaded on top of use_symfony_monolog_bundle_with_bugsnag_handler.php
    $containerConfigurator->parameters()
        ->set('app.bugsnag_handler_id', 'app.bugsnag_handler');

    $containerConfigurator->services()
        ->set('app.bugsnag_handler', BugsnagMonologHandler::class)
        ->autowire()
        ->arg('$level', param(ConfigParam::BugsnagHandlerLevel->value));

    $containerConfigurator->extension('monolog', [
        'handlers' => [
            'bugsnag' => [
                'type' => 'service',
                'id' => '%app.bugsnag_handler_id%',
                'channels' => ['other'],
            ],
        ],
    ]);
};
