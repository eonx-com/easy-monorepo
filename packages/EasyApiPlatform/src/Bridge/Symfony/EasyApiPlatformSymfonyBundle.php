<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony;

use EonX\EasyApiPlatform\Bridge\BridgeConstantsInterface;
use EonX\EasyApiPlatform\Bridge\Symfony\DependencyInjection\Compiler\EasyErrorHandlerCompilerPass;
use EonX\EasyApiPlatform\Bridge\Symfony\DependencyInjection\Compiler\ReadListenerCompilerPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyApiPlatformSymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_api_platform';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new EasyErrorHandlerCompilerPass());
        $container->addCompilerPass(new ReadListenerCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $parameters = $container->parameters();
        $parameters->set(
            BridgeConstantsInterface::PARAM_ADVANCED_SEARCH_FILTER_IRI_FIELDS,
            $config['advanced_search_filter']['iri_fields']
        );

        $container->import(__DIR__ . '/Resources/config/services.php');
        $container->import(__DIR__ . '/Resources/config/filters.php');

        if ($config['custom_paginator']['enabled']) {
            $container->import(__DIR__ . '/Resources/config/pagination.php');
        }

        if ($config['easy_error_handler']['enabled']) {
            $parameters->set(
                BridgeConstantsInterface::PARAM_EASY_ERROR_HANDLER_CUSTOM_SERIALIZER_EXCEPTIONS,
                $config['easy_error_handler']['custom_serializer_exceptions']
            );

            $container->import(__DIR__ . '/Resources/config/easy_error_handler.php');
        }

        if ($config['easy_bugsnag']['enabled']) {
            $container->import(__DIR__ . '/Resources/config/easy_bugsnag.php');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        /** @var string $apiPlatformBundleViewsFolder */
        $apiPlatformBundleViewsFolder = (new FileLocator(__DIR__ . '/Resources/views/bundles'))
            ->locate('ApiPlatformBundle');

        $builder->prependExtensionConfig('twig', [
            'paths' => [$apiPlatformBundleViewsFolder => 'ApiPlatform'],
        ]);
    }
}
