<?php

declare(strict_types=1);

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Authorization\AuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\CachedAuthorizationMatrixFactory;
use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Bridge\Symfony\DataCollector\SecurityContextDataCollector;
use EonX\EasySecurity\Bridge\Symfony\Factories\AuthenticationFailureResponseFactory;
use EonX\EasySecurity\Bridge\Symfony\Factories\MainSecurityContextConfiguratorFactory;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationFailureResponseFactoryInterface;
use EonX\EasySecurity\Bridge\Symfony\Security\ContextAuthenticator;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\MainSecurityContextConfigurator;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services
        ->defaults()
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
        ->arg('$cache', ref(BridgeConstantsInterface::SERVICE_AUTHORIZATION_MATRIX_CACHE))
        ->arg('$decorated', ref('easy_security.core_authorization_matrix_factory'));

    $services
        ->set(AuthorizationMatrixInterface::class)
        ->factory([ref(AuthorizationMatrixFactoryInterface::class), 'create']);

    // MainSecurityContextConfigurator
    $services
        ->set('easy_security.api_token_decoder', EasyApiTokenDecoderInterface::class)
        ->factory([ref(ApiTokenDecoderFactoryInterface::class), 'build'])
        ->args(['%' . BridgeConstantsInterface::PARAM_TOKEN_DECODER . '%']);

    $services
        ->set(MainSecurityContextConfiguratorFactory::class)
        ->arg('$apiTokenDecoder', ref('easy_security.api_token_decoder'));

    $services
        ->set(MainSecurityContextConfigurator::class)
        ->factory([ref(MainSecurityContextConfiguratorFactory::class), '__invoke'])
        ->call('withConfigurators', [tagged_iterator(BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR)])
        ->call('withModifiers', [tagged_iterator(BridgeConstantsInterface::TAG_CONTEXT_MODIFIER)]);

    // Symfony Security
    $services->set(AuthenticationFailureResponseFactoryInterface::class, AuthenticationFailureResponseFactory::class);

    $services->set(ContextAuthenticator::class);

    // DataCollector
    $services
        ->set(SecurityContextDataCollector::class)
        ->tag('data_collector', [
            'template' => '@EasySecurity/Collector/security_context_collector.html.twig',
            'id' => 'easy_security.security_context_collector',
        ]);
};
