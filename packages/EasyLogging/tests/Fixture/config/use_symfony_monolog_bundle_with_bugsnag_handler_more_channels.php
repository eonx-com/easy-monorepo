<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Loaded on top of use_symfony_monolog_bundle_with_bugsnag_handler_channels.php (like config/packages/prod/)
    $containerConfigurator->extension('easy_logging', [
        'bugsnag_handler_channels' => ['app'],
    ]);
};
