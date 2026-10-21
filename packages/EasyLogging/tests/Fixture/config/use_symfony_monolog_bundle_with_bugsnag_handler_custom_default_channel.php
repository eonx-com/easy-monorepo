<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Loaded on top of use_symfony_monolog_bundle_with_bugsnag_handler.php
    $containerConfigurator->extension('easy_logging', [
        'default_channel' => 'my-app',
    ]);
};
