<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony;

use EonX\EasyApiPlatform\Bridge\BridgeConstantsInterface;
use EonX\EasyApiPlatform\Bridge\Symfony\DependencyInjection\Compiler\ReadListenerCompilerPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyApiPlatformSymfonyBundle extends AbstractBundle
{
    private const EASY_API_PLATFORM_ADVANCED_SEARCH_FILTER_CONFIG = [
        'iri_fields' => BridgeConstantsInterface::PARAM_ADVANCED_SEARCH_FILTER_IRI_FIELDS,
    ];

    private const EASY_API_PLATFORM_BASE_CONFIG = [
        'custom_paginator_enabled' => BridgeConstantsInterface::PARAM_CUSTOM_PAGINATOR_ENABLED,
    ];

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ReadListenerCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::EASY_API_PLATFORM_BASE_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param, $config[$name]);
        }

        foreach (self::EASY_API_PLATFORM_ADVANCED_SEARCH_FILTER_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param, $config['advanced_search_filter'][$name]);
        }

        $container->import(__DIR__ . '/Resources/config/services.php');
        $container->import(__DIR__ . '/Resources/config/filters.php');

        if ($config['custom_paginator_enabled'] ?? true) {
            $container->import(__DIR__ . '/Resources/config/pagination.php');
        }
    }
}
