<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Monolog\Level;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Loaded on top of use_symfony_monolog_bundle_with_bugsnag_handler.php
    $containerConfigurator->extension('easy_logging', [
        'bugsnag_handler_level' => Level::Error->value,
    ]);
};
