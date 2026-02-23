<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_logging', [
        'default_channel' => 'my-app',
        'sensitive_data_sanitizer' => true,
    ]);
};
