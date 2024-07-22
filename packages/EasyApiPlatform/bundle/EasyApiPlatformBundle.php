<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bundle;

use EonX\EasyApiPlatform\Bundle\CompilerPass\EasyErrorHandlerCompilerPass;
use EonX\EasyApiPlatform\Bundle\CompilerPass\ReadListenerCompilerPass;
use EonX\EasyApiPlatform\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyApiPlatformBundle extends AbstractBundle
{
    private const ADVANCED_SEARCH_FILTER_CONFIG = [
        'iri_fields' => ConfigParam::AdvancedSearchFilterIriFields,
    ];

    private const CUSTOM_PAGINATOR_CONFIG = [
        'enabled' => ConfigParam::CustomPaginatorEnabled,
    ];

    private const EASY_ERROR_HANDLER_CONFIG = [
        'custom_serializer_exceptions' => ConfigParam::EasyErrorHandlerCustomSerializerExceptions,
        'enabled' => ConfigParam::EasyErrorHandlerEnabled,
        'report_exceptions_to_bugsnag' => ConfigParam::EasyErrorHandlerReportExceptionsToBugsnag,
    ];

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new EasyErrorHandlerCompilerPass())
            ->addCompilerPass(new ReadListenerCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::ADVANCED_SEARCH_FILTER_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config['advanced_search_filter'][$name]);
        }

        foreach (self::CUSTOM_PAGINATOR_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config['custom_paginator'][$name]);
        }

        foreach (self::EASY_ERROR_HANDLER_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config['easy_error_handler'][$name]);
        }

        $container->import('config/services.php');
        $container->import('config/filters.php');

        if ($config['custom_paginator']['enabled']) {
            $container->import('config/pagination.php');
        }

        if ($config['easy_error_handler']['enabled']) {
            $container->import('config/easy_error_handler.php');
        }

        if ($config['easy_error_handler']['report_exceptions_to_bugsnag'] === false) {
            $container->import('config/bugsnag.php');
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
