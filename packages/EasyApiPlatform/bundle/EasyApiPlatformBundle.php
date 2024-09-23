<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bundle;

use EonX\EasyApiPlatform\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyApiPlatformBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container
            ->parameters()
            ->set(ConfigParam::AdvancedSearchFilterIriFields->value, $config['advanced_search_filter']['iri_fields']);

        $container->import('config/filters.php');

        if ($config['custom_paginator']['enabled']) {
            $container->import('config/pagination.php');
        }

        if ($config['return_not_found_on_read_operations']['enabled']) {
            $container->import('config/state_provider.php');
        }

        $this->registerEasyErrorHandlerConfiguration($config, $container, $builder);
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        /** @var string $apiPlatformBundleViewsFolder */
        $apiPlatformBundleViewsFolder = (new FileLocator(__DIR__ . '/templates/bundles'))
            ->locate('ApiPlatformBundle');

        $builder->prependExtensionConfig('twig', [
            'paths' => [$apiPlatformBundleViewsFolder => 'ApiPlatform'],
        ]);
    }

    private function registerEasyErrorHandlerConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['easy_error_handler'];

        if ($config['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(
                ConfigParam::EasyErrorHandlerCustomSerializerExceptions->value,
                $config['custom_serializer_exceptions']
            );

        $container->import('config/easy_error_handler.php');

        // If report_exceptions_to_bugsnag is set to false, import the bugsnag configuration with exception ignorer
        if ($config['report_exceptions_to_bugsnag'] === false) {
            $container->import('config/bugsnag.php');
        }
    }
}
