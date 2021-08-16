<?php

declare(strict_types=1);

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Authorization\AuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\CachedAuthorizationMatrixFactory;
use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Bridge\Symfony\DataCollector\SecurityContextDataCollector;
use EonX\EasySecurity\Bridge\Symfony\Factories\AuthenticationFailureResponseFactory;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface;
use EonX\EasySecurity\Bridge\Symfony\Listeners\FromRequestSecurityContextConfiguratorListener;
use EonX\EasySecurity\Bridge\Symfony\Security\ContextAuthenticator;
use EonX\EasySecurity\Bridge\Symfony\Security\SecurityContextAuthenticator;
use EonX\EasySecurity\DeferredSecurityContextProvider;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\DeferredSecurityContextProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use EonX\EasySecurity\SecurityContextFactory;
use EonX\EasySecurity\SecurityContextResolver;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // ApiTokenDecoder
    $services
        ->set(BridgeConstantsInterface::SERVICE_API_TOKEN_DECODER, ApiTokenDecoderInterface::class)
        ->factory([ref(ApiTokenDecoderFactoryInterface::class), 'build'])
        ->args(['%' . BridgeConstantsInterface::PARAM_TOKEN_DECODER . '%']);

    // Authorization
    $services->set(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE, ArrayAdapter::class);

    $services
        ->set('easy_security.core_authorization_matrix_factory', AuthorizationMatrixFactory::class)
        ->arg('$rolesProviders', tagged_iterator(BridgeConstantsInterface::TAG_ROLES_PROVIDER))
        ->arg('$permissionsProviders', tagged_iterator(BridgeConstantsInterface::TAG_PERMISSIONS_PROVIDER));

    $services
        ->set(AuthorizationMatrixFactoryInterface::class, CachedAuthorizationMatrixFactory::class)
        ->arg('$cache', ref(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE))
        ->arg('$decorated', ref('easy_security.core_authorization_matrix_factory'));

    $services
        ->set(AuthorizationMatrixInterface::class)
        ->factory([ref(AuthorizationMatrixFactoryInterface::class), 'create']);

    // DataCollector
    $services
        ->set(SecurityContextDataCollector::class)
        ->arg('$configurators', tagged_iterator(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR))
        ->tag('data_collector', [
            'template' => '@EasySecuritySymfony/Collector/security_context_collector.html.twig',
            'id' => SecurityContextDataCollector::NAME,
        ]);

    // Deferred Security Provider
    $services->set(DeferredSecurityContextProviderInterface::class, DeferredSecurityContextProvider::class);

    // Request
    $services
        ->set(FromRequestSecurityContextConfiguratorListener::class)
        ->arg('$configurators', tagged_iterator(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR));

    // Resolver
    $services->set(SecurityContextResolverInterface::class, SecurityContextResolver::class);

    // SecurityContextFactory
    $services->set(SecurityContextFactoryInterface::class, SecurityContextFactory::class);

    // Symfony Security
    $services
        ->set(AuthenticationFailureResponseFactoryInterface::class, AuthenticationFailureResponseFactory::class)
        ->tag('monolog.logger', ['channel' => BridgeConstantsInterface::LOG_CHANNEL]);

    $services->set(ContextAuthenticator::class);

    // New Symfony Security
    if (\interface_exists(PassportInterface::class)) {
        $services->set(SecurityContextAuthenticator::class);
    }
};
