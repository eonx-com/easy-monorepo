<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('EonX\\EasyActivity\\Tests\\Fixture\\App\\', '../src/*')
        ->exclude([
            '../src/Kernel/ApplicationKernel.php',
            '../src/**/ApiResource',
            '../src/**/Config',
            '../src/**/DataTransferObject',
            '../src/**/Entity',
        ]);

    // Register Symfony's native PHP serializer for messenger
    $services->set('messenger.transport.native_php_serializer', PhpSerializer::class);
};
