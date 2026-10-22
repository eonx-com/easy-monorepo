<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Loaded on top of bugsnag_handler.php
    $containerConfigurator->extension('monolog', [
        'handlers' => [
            'bugsnag' => [
                'type' => 'service',
                'id' => BugsnagMonologHandler::class,
                'channels' => ['other'],
            ],
        ],
    ]);
};
