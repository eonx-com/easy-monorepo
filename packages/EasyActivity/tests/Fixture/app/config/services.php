<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Listeners\EntityEventListener;
use EonX\EasyDoctrine\ORM\Decorators\EntityManagerDecorator;

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

    $services->set(EntityManagerDecorator::class)
        ->arg('$decorated', service('.inner'))
        ->decorate('doctrine.orm.default_entity_manager');

    $services->set(EntityEventListener::class)
        ->arg('$trackableEntities', param('easy_doctrine.deferred_dispatcher_entities'))
        ->tag('doctrine.event_subscriber', ['connection' => 'default']);
};
