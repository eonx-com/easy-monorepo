<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Loaded on top of bugsnag_handler.php
    $containerConfigurator->extension('easy_logging', [
        'bugsnag_handler' => [
            'channels' => ['other'],
        ],
    ]);
};
