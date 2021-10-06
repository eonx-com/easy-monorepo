<?php

declare(strict_types=1);

use EonX\EasyDoctrine\Bridge\EasyErrorHandler\TransactionalExceptionListener;
use EonX\EasyDoctrine\Events\TransactionalExceptionEvent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(TransactionalExceptionListener::class)
        ->tag('kernel.event_listener', ['event' => TransactionalExceptionEvent::class]);
};
