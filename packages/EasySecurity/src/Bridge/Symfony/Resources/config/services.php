<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use EonX\EasySecurity\Authorization\AuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\CachedAuthorizationMatrixFactory;
use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Bridge\Symfony\DataCollector\SecurityContextDataCollector;
use EonX\EasySecurity\Bridge\Symfony\Factories\AuthenticationFailureResponseFactory;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface;
use EonX\EasySecurity\Bridge\Symfony\Listeners\FromRequestSecurityContextConfiguratorListener;
use EonX\EasySecurity\Bridge\Symfony\Security\SecurityContextAuthenticator;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use EonX\EasySecurity\SecurityContextFactory;
use EonX\EasySecurity\SecurityContextResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Authorization
    $services->set(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE, ArrayAdapter::class);

    $services
        ->set('easy_security.core_authorization_matrix_factory', AuthorizationMatrixFactory::class)
        ->arg('$rolesProviders', tagged_iterator(BridgeConstantsInterface::TAG_ROLES_PROVIDER))
        ->arg('$permissionsProviders', tagged_iterator(BridgeConstantsInterface::TAG_PERMISSIONS_PROVIDER));

    $services
        ->set(AuthorizationMatrixFactoryInterface::class, CachedAuthorizationMatrixFactory::class)
        ->arg('$cache', service(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE))
        ->arg('$decorated', service('easy_security.core_authorization_matrix_factory'));

    // DataCollector
    $services
        ->set(SecurityContextDataCollector::class)
        ->arg('$configurators', tagged_iterator(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR))
        ->tag('data_collector', [
            'id' => SecurityContextDataCollector::NAME,
            'template' => '@EasySecuritySymfony/Collector/security_context_collector.html.twig',
        ]);

    // Request
    $services
        ->set(FromRequestSecurityContextConfiguratorListener::class)
        ->arg('$configurators', tagged_iterator(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR))
        ->tag('kernel.event_listener', ['priority' => 9999]);

    // Resolver
    $securityContextResolver = $services->set(SecurityContextResolverInterface::class, SecurityContextResolver::class);

    // SecurityContextFactory
    $services->set(SecurityContextFactoryInterface::class, SecurityContextFactory::class);

    // Symfony Security
    $responseFactory = $services
        ->set(AuthenticationFailureResponseFactoryInterface::class, AuthenticationFailureResponseFactory::class);
    $services->set(SecurityContextAuthenticator::class);

    // Logger
    if (\interface_exists(LoggerFactoryInterface::class)) {
        $services
            ->set(BridgeConstantsInterface::SERVICE_LOGGER, LoggerInterface::class)
            ->factory([service(LoggerFactoryInterface::class), 'create'])
            ->args([BridgeConstantsInterface::LOG_CHANNEL]);

        $securityContextResolver
            ->arg('$logger', service(BridgeConstantsInterface::SERVICE_LOGGER));
        $responseFactory
            ->arg('$logger', service(BridgeConstantsInterface::SERVICE_LOGGER));
    }
};
