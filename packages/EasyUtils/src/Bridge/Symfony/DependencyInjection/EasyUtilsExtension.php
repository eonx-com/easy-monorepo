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
    public const MATH_CONFIG = [
        'format_decimal_separator' => BridgeConstantsInterface::PARAM_MATH_FORMAT_DECIMAL_SEPARATOR,
        'format_thousands_separator' => BridgeConstantsInterface::PARAM_MATH_FORMAT_THOUSANDS_SEPARATOR,
        'round_mode' => BridgeConstantsInterface::PARAM_MATH_ROUND_MODE,
        'round_precision' => BridgeConstantsInterface::PARAM_MATH_ROUND_PRECISION,
        'scale' => BridgeConstantsInterface::PARAM_MATH_SCALE,
    ];

    private const SENSITIVE_DATA_CONFIG = [
        'keys_to_mask' => BridgeConstantsInterface::PARAM_SENSITIVE_DATA_KEYS_TO_MASK,
        'mask_pattern' => BridgeConstantsInterface::PARAM_SENSITIVE_DATA_MASK_PATTERN,
        'use_default_keys_to_mask' => BridgeConstantsInterface::PARAM_SENSITIVE_DATA_USE_DEFAULT_KEYS_TO_MASK,
    ];

    private const STRING_TRIMMER_CONFIG = [
        'except_keys' => BridgeConstantsInterface::PARAM_STRING_TRIMMER_EXCEPT_KEYS,
    ];

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach (self::MATH_CONFIG as $name => $param) {
            $container->setParameter($param, $config['math'][$name]);
        }

        foreach (self::SENSITIVE_DATA_CONFIG as $name => $param) {
            $container->setParameter($param, $config['sensitive_data'][$name]);
        }

        foreach (self::STRING_TRIMMER_CONFIG as $name => $param) {
            $container->setParameter($param, $config['string_trimmer'][$name]);
        }

        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));
        $loader->load('services.php');

        // SensitiveData
        if ($config['sensitive_data']['enabled'] ?? true) {
            $container
                ->registerForAutoconfiguration(ObjectTransformerInterface::class)
                ->addTag(BridgeConstantsInterface::TAG_SENSITIVE_DATA_OBJECT_TRANSFORMER);

            $container
                ->registerForAutoconfiguration(StringSanitizerInterface::class)
                ->addTag(BridgeConstantsInterface::TAG_SENSITIVE_DATA_STRING_SANITIZER);

            if ($config['sensitive_data']['use_default_object_transformers'] ?? true) {
                $loader->load('sensitive_data_default_object_transformers.php');
            }

            if ($config['sensitive_data']['use_default_string_sanitizers'] ?? true) {
                $loader->load('sensitive_data_default_string_sanitizers.php');
            }

            $loader->load('sensitive_data.php');
        }

        // StringTrimmer
        if ($config['string_trimmer']['enabled'] ?? false) {
            $loader->load('string_trimmer.php');
        }
    }
}
