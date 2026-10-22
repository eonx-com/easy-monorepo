<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Monolog\Level;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Loaded on top of bugsnag_client.php only: like canBeEnabled(), the "level" key alone enables the handler
    $containerConfigurator->extension('easy_logging', [
        'bugsnag_handler' => [
            'level' => Level::Error->value,
        ],
    ]);
};
