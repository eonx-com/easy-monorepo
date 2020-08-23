<?php

declare(strict_types=1);

use EonX\EasySecurity\Authorization\AuthorizationMatrixFactory;
use EonX\EasySecurity\Bridge\Symfony\DataCollector\SecurityContextDataCollector;
use EonX\EasySecurity\Bridge\Symfony\Factories\AuthenticationFailureResponseFactory;
use EonX\EasySecurity\Bridge\Symfony\Helpers\DeferredSecurityContextResolver;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredContextResolverInterface;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredSecurityContextResolverInterface;
use EonX\EasySecurity\Bridge\Symfony\Security\ContextAuthenticator;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use EonX\EasySecurity\SecurityContextFactory;
use EonX\EasySecurity\SecurityContextResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(AuthorizationMatrixFactoryInterface::class, AuthorizationMatrixFactory::class)
        ->arg('$rolesProviders', tagged_iterator('easy_security.roles_provider'))
        ->arg('$permissionsProviders', tagged_iterator('easy_security.permissions_provider'));

    $services->set(AuthorizationMatrixInterface::class)
        ->factory([ref(AuthorizationMatrixFactoryInterface::class), 'create']);

    $services->set(ContextAuthenticator::class);

    $services->set(AuthenticationFailureResponseFactoryInterface::class, AuthenticationFailureResponseFactory::class);

    $services->set(SecurityContextFactoryInterface::class, SecurityContextFactory::class);

    $services->set(SecurityContextResolverInterface::class, SecurityContextResolver::class);

    $services->set(DeferredSecurityContextResolverInterface::class, DeferredSecurityContextResolver::class);

    $services->alias(DeferredContextResolverInterface::class, DeferredSecurityContextResolverInterface::class);

    $services->set(SecurityContextDataCollector::class)
        ->tag('data_collector', [
            'template' => '@EasySecurity/Collector/security_context_collector.html.twig',
            'id' => 'easy_security.security_context_collector',
        ]);
};
