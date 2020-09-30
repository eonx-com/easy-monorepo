<?php

declare(strict_types=1);

use EonX\EasyRequestId\Bridge\BridgeConstantsInterface;
use EonX\EasyRequestId\Bridge\EasyWebhook\RequestIdWebhookConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(RequestIdWebhookConfigurator::class)
        ->call('setCorrelationIdKey', ['%' . BridgeConstantsInterface::PARAM_CORRELATION_ID_KEY . '%'])
        ->call('setRequestIdKey', ['%' . BridgeConstantsInterface::PARAM_REQUEST_ID_KEY . '%']);
};
