<?php

declare(strict_types=1);

use ApiPlatform\Core\Bridge\Symfony\Validator\EventListener\ValidationExceptionListener;
use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Symfony\Builders\ApiPlatformBuilderProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ApiPlatformBuilderProvider::class)
        ->arg('$keys', '%' . BridgeConstantsInterface::PARAM_RESPONSE_KEYS . '%');

    // Drop tags of ValidationExceptionListener
    $services->set(ValidationExceptionListener::class);
};
