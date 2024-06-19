<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bundle;

use EonX\EasyApiPlatform\Bundle\CompilerPass\ReadListenerCompilerPass;
use EonX\EasyApiPlatform\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyApiPlatformBundle extends AbstractBundle
{
    private const EASY_API_PLATFORM_ADVANCED_SEARCH_FILTER_CONFIG = [
        'iri_fields' => ConfigParam::AdvancedSearchFilterIriFields,
    ];

    private const EASY_API_PLATFORM_BASE_CONFIG = [
        'custom_paginator_enabled' => ConfigParam::CustomPaginatorEnabled,
    ];

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ReadListenerCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::EASY_API_PLATFORM_BASE_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config[$name]);
        }

        foreach (self::EASY_API_PLATFORM_ADVANCED_SEARCH_FILTER_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config['advanced_search_filter'][$name]);
        }

        $container->import(__DIR__ . '/config/services.php');
        $container->import(__DIR__ . '/config/filters.php');

        if ($config['custom_paginator_enabled'] ?? true) {
            $container->import(__DIR__ . '/config/pagination.php');
        }
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
}
