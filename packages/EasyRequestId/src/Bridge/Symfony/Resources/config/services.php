<?php

declare(strict_types=1);

use EonX\EasyRequestId\Bridge\BridgeConstantsInterface;
use EonX\EasyRequestId\Bridge\Symfony\Listeners\RequestListener;
use EonX\EasyRequestId\Bridge\Symfony\Messenger\SendMessageToTransportsListener;
use EonX\EasyRequestId\Bridge\Symfony\Messenger\WorkerMessageReceivedListener;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\RequestIdService;
use EonX\EasyRequestId\UuidV4FallbackResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Fallback
    $services->set(FallbackResolverInterface::class, UuidV4FallbackResolver::class);

    // RequestIdService
    $services
        ->set(RequestIdServiceInterface::class, RequestIdService::class)
        ->arg('$correlationIdHeaderName', '%' . BridgeConstantsInterface::PARAM_HTTP_HEADER_CORRELATION_ID . '%')
        ->arg('$requestIdHeaderName', '%' . BridgeConstantsInterface::PARAM_HTTP_HEADER_REQUEST_ID . '%');

    // Listener
    $services
        ->set(RequestListener::class)
        ->tag('kernel.event_listener', [
            'priority' => 10000,
        ]);

    // Messenger
    $services
        ->set(SendMessageToTransportsListener::class)
        ->tag('kernel.event_listener');

    $services
        ->set(WorkerMessageReceivedListener::class)
        ->tag('kernel.event_listener');
};
