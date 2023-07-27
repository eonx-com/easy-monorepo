<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\EasyWebhook\WebhookFinalFailedListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(WebhookFinalFailedListener::class)
        ->tag('kernel.event_listener');
};
