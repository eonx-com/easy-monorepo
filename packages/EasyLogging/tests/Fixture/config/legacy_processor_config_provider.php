<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Tests\Stub\Processor\SelfConfigProvidingProcessorStub;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->set(SelfConfigProvidingProcessorStub::class);
};
