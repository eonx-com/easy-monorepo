<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use EonX\EasySecurity\Authorization\Factory\AuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\Factory\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Authorization\Factory\CachedAuthorizationMatrixFactory;
use EonX\EasySecurity\Bundle\Enum\BundleParam;
use EonX\EasySecurity\Bundle\Enum\ConfigServiceId;
use EonX\EasySecurity\Bundle\Enum\ConfigTag;
use EonX\EasySecurity\Common\DataCollector\SecurityContextDataCollector;
use EonX\EasySecurity\Common\Factory\SecurityContextFactory;
use EonX\EasySecurity\Common\Factory\SecurityContextFactoryInterface;
use EonX\EasySecurity\Common\Listener\FromRequestSecurityContextConfiguratorListener;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolver;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;
use EonX\EasySecurity\SymfonySecurity\Authenticator\SecurityContextAuthenticator;
use EonX\EasySecurity\SymfonySecurity\Factory\AuthenticationFailureResponseFactory;
use EonX\EasySecurity\SymfonySecurity\Factory\AuthenticationFailureResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Authorization
    $services->set(ConfigServiceId::AuthorizationMatrixCache->value, ArrayAdapter::class);

    $services
        ->set('easy_security.core_authorization_matrix_factory', AuthorizationMatrixFactory::class)
        ->arg('$rolesProviders', tagged_iterator(ConfigTag::RolesProvider->value))
        ->arg('$permissionsProviders', tagged_iterator(ConfigTag::PermissionsProvider->value));

    $services
        ->set(AuthorizationMatrixFactoryInterface::class, CachedAuthorizationMatrixFactory::class)
        ->arg('$cache', service(ConfigServiceId::AuthorizationMatrixCache->value))
        ->arg('$decorated', service('easy_security.core_authorization_matrix_factory'));

    // DataCollector
    $services
        ->set(SecurityContextDataCollector::class)
        ->arg('$configurators', tagged_iterator(ConfigTag::ContextConfigurator->value))
        ->tag('data_collector', [
            'id' => 'easy_security',
            'template' => '@EasySecurity/collector/security_context_collector.html.twig',
        ]);

    // Request
    $services
        ->set(FromRequestSecurityContextConfiguratorListener::class)
        ->arg('$configurators', tagged_iterator(ConfigTag::ContextConfigurator->value))
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
            ->set(ConfigServiceId::Logger->value, LoggerInterface::class)
            ->factory([service(LoggerFactoryInterface::class), 'create'])
            ->args([BundleParam::LogChannel->value]);

        $securityContextResolver
            ->arg('$logger', service(ConfigServiceId::Logger->value));
        $responseFactory
            ->arg('$logger', service(ConfigServiceId::Logger->value));
    }
};
