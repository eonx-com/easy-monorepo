<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Loaded on top of bugsnag_handler_channels.php (like config/packages/prod/)
    $containerConfigurator->extension('easy_logging', [
        'bugsnag_handler' => [
            'channels' => ['app'],
        ],
    ]);
};
