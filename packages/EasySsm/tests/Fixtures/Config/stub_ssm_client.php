<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySsm\Services\Aws\SsmClientInterface;
use EonX\EasySsm\Tests\Stubs\SsmClientStub;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SsmClientInterface::class, SsmClientStub::class)
        ->arg('$parameters', [[
            'name' => 'param',
            'type' => 'string',
            'value' => 'value',
        ]]);
};
