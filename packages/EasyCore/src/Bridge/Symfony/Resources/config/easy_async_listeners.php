<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Events\JobLogFailedEvent;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Listeners\AddValidationErrorsOnJobLogListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddValidationErrorsOnJobLogListener::class)
        ->tag('kernel.event_listener', [
            'event' => JobLogFailedEvent::class,
        ]);
};
