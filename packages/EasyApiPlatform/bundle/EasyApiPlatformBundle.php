<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bundle;

use EonX\EasyApiPlatform\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

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

        if ($config['output_sanitizer']['enabled']) {
            $container->import('config/output_sanitizer.php');
        }

        if ($config['return_not_found_on_read_operations']['enabled']) {
            $container->import('config/state_provider.php');
        }

        $this->registerEasyErrorHandlerConfiguration($config, $container, $builder);
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        /** @var string $apiPlatformBundleViewsFolder */
        $apiPlatformBundleViewsFolder = new FileLocator(__DIR__ . '/templates/bundles')
            ->locate('ApiPlatformBundle');

        $builder->prependExtensionConfig('twig', [
            'paths' => [$apiPlatformBundleViewsFolder => 'ApiPlatform'],
        ]);

        // The use_symfony_listeners should be true as we use controllers and rely on Symfony event listeners.
        // See https://github.com/api-platform/core/blob/main/CHANGELOG.md#v332
        $builder->prependExtensionConfig('api_platform', [
            'use_symfony_listeners' => true,
        ]);

        if ($this->isBundleEnabled('EasyErrorHandlerBundle', $builder)) {
            $easyErrorHandlerEnabled = true;
            /** @var array $config */
            foreach ($builder->getExtensionConfig('easy_api_platform') as $config) {
                if (($config['easy_error_handler']['enabled'] ?? true) !== true) {
                    $easyErrorHandlerEnabled = false;

                    break;
                }
            }

            if ($easyErrorHandlerEnabled) {
                $builder->prependExtensionConfig('api_platform', [
                    'defaults' => [
                        'denormalization_context' => [
                            DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
                        ],
                    ],
                ]);
            }
        }
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $builder): bool
    {
        /** @var array $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }

    private function registerEasyErrorHandlerConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($this->isBundleEnabled('EasyErrorHandlerBundle', $builder) === false) {
            return;
        }

        $config = $config['easy_error_handler'];

        if ($config['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(
                ConfigParam::EasyErrorHandlerCustomSerializerExceptions->value,
                $config['custom_serializer_exceptions']
            )
            ->set(ConfigParam::EasyErrorHandlerValidationErrorCode->value, $config['validation_error_code']);

        $container->import('config/easy_error_handler.php');

        // If report_exceptions_to_bugsnag is set to false, import the bugsnag configuration with exception ignorer
        if ($config['report_exceptions_to_bugsnag'] === false) {
            $container->import('config/bugsnag.php');
        }
    }
}
