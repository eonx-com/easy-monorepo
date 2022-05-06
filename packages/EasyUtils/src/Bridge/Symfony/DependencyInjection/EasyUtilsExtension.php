<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\DependencyInjection;

use EonX\EasyUtils\Bridge\BridgeConstantsInterface;
use EonX\EasyUtils\SensitiveData\ObjectTransformerInterface;
use EonX\EasyUtils\SensitiveData\StringSanitizerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyUtilsExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));

        foreach (BridgeConstantsInterface::MATH_PARAMS as $mathParam) {
            $container->setParameter($mathParam, $config[$mathParam] ?? null);
        }

        $loader->load('services.php');

        // SensitiveData
        if ($config['sensitive_data']['enabled'] ?? true) {
            $container
                ->registerForAutoconfiguration(ObjectTransformerInterface::class)
                ->addTag(BridgeConstantsInterface::TAG_SENSITIVE_DATA_OBJECT_TRANSFORMER);

            $container
                ->registerForAutoconfiguration(StringSanitizerInterface::class)
                ->addTag(BridgeConstantsInterface::TAG_SENSITIVE_DATA_STRING_SANITIZER);

            foreach (BridgeConstantsInterface::SENSITIVE_DATA_PARAMS as $sensitiveDataParam => $configName) {
                $container->setParameter($sensitiveDataParam, $config['sensitive_data'][$configName]);
            }

            if ($config['sensitive_data']['use_default_object_transformers'] ?? true) {
                $loader->load('sensitive_data_default_object_transformers.php');
            }

            if ($config['sensitive_data']['use_default_string_sanitizers'] ?? true) {
                $loader->load('sensitive_data_default_string_sanitizers.php');
            }

            $loader->load('sensitive_data.php');
        }
    }
}
