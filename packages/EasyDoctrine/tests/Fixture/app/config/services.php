<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsAuthTokenProviderInterface;
use EonX\EasyDoctrine\Tests\Fixture\App\Processor\WithEntityManagerProcessor;
use EonX\EasyDoctrine\Tests\Fixture\App\Provider\DummyAwsRdsAuthTokenProvider;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyTest\EasyEventDispatcher\Dispatcher\EventDispatcherStub;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('EonX\\EasyDoctrine\\Tests\\Fixture\\App\\', '../src/*')
        ->exclude([
            '../src/Kernel/ApplicationKernel.php',
            '../src/**/ApiResource',
            '../src/**/Config',
            '../src/**/DataTransferObject',
            '../src/**/Entity',
        ]);

    $services->set(AwsRdsAuthTokenProviderInterface::class, DummyAwsRdsAuthTokenProvider::class);

    $services->set(WithEntityManagerProcessor::class)
        ->public();

    $services->set(EventDispatcherStub::class)
        ->decorate(EventDispatcherInterface::class)
        ->arg('$decorated', service('.inner'))
        ->public();
};
