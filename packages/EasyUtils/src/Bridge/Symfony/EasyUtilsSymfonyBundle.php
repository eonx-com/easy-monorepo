<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony;

use EonX\EasyUtils\Bridge\BridgeConstantsInterface;
use EonX\EasyUtils\SensitiveData\ObjectTransformerInterface;
use EonX\EasyUtils\SensitiveData\StringSanitizerInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyUtilsSymfonyBundle extends AbstractBundle
{
    public const MATH_CONFIG = [
        'format_decimal_separator' => BridgeConstantsInterface::PARAM_MATH_FORMAT_DECIMAL_SEPARATOR,
        'format_thousands_separator' => BridgeConstantsInterface::PARAM_MATH_FORMAT_THOUSANDS_SEPARATOR,
        'round_mode' => BridgeConstantsInterface::PARAM_MATH_ROUND_MODE,
        'round_precision' => BridgeConstantsInterface::PARAM_MATH_ROUND_PRECISION,
        'scale' => BridgeConstantsInterface::PARAM_MATH_SCALE,
    ];

    private const STRING_TRIMMER_CONFIG = [
        'except_keys' => BridgeConstantsInterface::PARAM_STRING_TRIMMER_EXCEPT_KEYS,
    ];

    protected string $extensionAlias = 'easy_utils';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::MATH_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param, $config['math'][$name]);
        }

        foreach (self::STRING_TRIMMER_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param, $config['string_trimmer'][$name]);
        }

        $container->import(__DIR__ . '/Resources/config/services.php');

        // SensitiveData
        if ($config['sensitive_data']['enabled'] ?? true) {
            $this->configureSensitiveDataSanitizer($config, $container, $builder);
        }

        // StringTrimmer
        if ($config['string_trimmer']['enabled'] ?? false) {
            $container->import(__DIR__ . '/Resources/config/string_trimmer.php');
        }
    }

    private function configureSensitiveDataSanitizer(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $builder
            ->registerForAutoconfiguration(ObjectTransformerInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_SENSITIVE_DATA_OBJECT_TRANSFORMER);

        $builder
            ->registerForAutoconfiguration(StringSanitizerInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_SENSITIVE_DATA_STRING_SANITIZER);

        if ($config['sensitive_data']['use_default_object_transformers'] ?? true) {
            $container->import(__DIR__ . '/Resources/config/sensitive_data_default_object_transformers.php');
        }

        if ($config['sensitive_data']['use_default_string_sanitizers'] ?? true) {
            $container->import(__DIR__ . '/Resources/config/sensitive_data_default_string_sanitizers.php');
        }

        $container->import(__DIR__ . '/Resources/config/sensitive_data.php');

        $defaultKeysToMask = ($config['sensitive_data']['use_default_keys_to_mask'] ?? false)
            ? BridgeConstantsInterface::SENSITIVE_DATA_DEFAULT_KEYS_TO_MASK
            : [];

        $keysToMask = $config['sensitive_data']['keys_to_mask'] ?? [];

        $container
            ->parameters()
            ->set(
                BridgeConstantsInterface::PARAM_SENSITIVE_DATA_KEYS_TO_MASK,
                \array_unique(\array_merge($defaultKeysToMask, $keysToMask))
            );

        $container
            ->parameters()
            ->set(
                BridgeConstantsInterface::PARAM_SENSITIVE_DATA_MASK_PATTERN,
                $config['sensitive_data']['mask_pattern']
                ?? BridgeConstantsInterface::SENSITIVE_DATA_DEFAULT_MASK_PATTERN
            );
    }
}
