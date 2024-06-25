<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\EntityEvent\EntityManager\WithEventsEntityManager;
use EonX\EasyDoctrine\EntityEvent\Listener\EntityEventListener;

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

    $services->set(WithEventsEntityManager::class)
        ->arg('$decorated', service('.inner'))
        ->decorate('doctrine.orm.default_entity_manager');

    $services->set(EntityEventListener::class)
        ->arg('$trackableEntities', param('easy_doctrine.deferred_dispatcher_entities'))
        ->tag('doctrine.event_subscriber', ['connection' => 'default']);
};
