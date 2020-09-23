<?php

declare(strict_types=1);

use EonX\EasyRequestId\Bridge\BridgeConstantsInterface;
use EonX\EasyRequestId\Bridge\Symfony\Factories\RequestIdServiceFactory;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\RequestIdService;
use EonX\EasyRequestId\UuidV4FallbackResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Fallback
    $services->set(FallbackResolverInterface::class, UuidV4FallbackResolver::class);

    // RequestIdService + Factory
    $services
        ->set(RequestIdServiceFactory::class)
        ->arg('$correlationIdResolvers', tagged_iterator(BridgeConstantsInterface::TAG_CORRELATION_ID_RESOLVER))
        ->arg('$requestIdResolvers', tagged_iterator(BridgeConstantsInterface::TAG_REQUEST_ID_RESOLVER));

    $services
        ->set(RequestIdServiceInterface::class, RequestIdService::class)
        ->factory([ref(RequestIdServiceFactory::class), '__invoke']);
};
