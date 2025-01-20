<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Common\Listener\OutputSanitizerListener;
use EonX\EasyApiPlatform\Tests\Fixture\App\BugsnagExceptionIgnorer\Helper\IgnorerHelper;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigTag;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('EonX\\EasyApiPlatform\\Tests\\Fixture\\App\\', '../src/*')
        ->exclude([
            '../src/Kernel/ApplicationKernel.php',
            '../src/**/ApiResource',
            '../src/**/Config',
            '../src/**/DataTransferObject',
            '../src/**/Entity',
        ]);

    $services->alias('test.property_accessor', 'property_accessor')
        ->public();

    $services->set(IgnorerHelper::class)
        ->arg('$exceptionIgnorers', tagged_iterator(ConfigTag::BugsnagExceptionIgnorer->value))
        ->public();
};
