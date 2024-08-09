<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\AwsRds\Provider\AwsRdsAuthTokenProviderInterface;
use EonX\EasyDoctrine\Bundle\Enum\ConfigParam;
use EonX\EasyDoctrine\Common\Listener\TimestampableListener;
use EonX\EasyDoctrine\EntityEvent\EntityManager\WithEventsEntityManager;
use EonX\EasyDoctrine\EntityEvent\Listener\EntityEventListener;
use EonX\EasyDoctrine\Tests\Fixture\App\Dispatcher\EventDispatcher;
use EonX\EasyDoctrine\Tests\Fixture\App\Processor\WithEntityManagerProcessor;
use EonX\EasyDoctrine\Tests\Fixture\App\Provider\DummyAwsRdsAuthTokenProvider;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;

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

    $services
        ->set(EventDispatcherInterface::class, EventDispatcher::class)
        ->alias(EventDispatcherInterface::class, EventDispatcher::class);

    $services->set(TimestampableListener::class);

    $services->set(WithEventsEntityManager::class)
        ->arg('$decorated', service('.inner'))
        ->decorate('doctrine.orm.default_entity_manager');

    $services
        ->set(EntityEventListener::class)
        ->arg('$trackableEntities', param(ConfigParam::DeferredDispatcherEntities->value));
};
