<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig): void {
    $frameworkConfig
        ->secret('test-secret-for-testing')
        ->test(true)
        ->serializer()
            ->enabled(true);

    $frameworkConfig
        ->messenger()
            ->enabled(true)
            ->defaultBus('messenger.bus.default')
            ->transport('async', 'in-memory://');

    $frameworkConfig
        ->uid()
            ->defaultUuidVersion(6);
};
