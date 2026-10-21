<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_logging', [
        'use_symfony_monolog_bundle' => true,
        // Makes prependExtension() prepend the monolog handler too: the targeted exception must still surface
        'bugsnag_handler' => true,
    ]);
};
