<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Bundle;

use EonX\EasyUtils\Bundle\Enum\BundleParam;
use EonX\EasyUtils\Bundle\Enum\ConfigParam;
use EonX\EasyUtils\Bundle\Enum\ConfigTag;
use EonX\EasyUtils\SensitiveData\Sanitizer\StringSanitizerInterface;
use EonX\EasyUtils\SensitiveData\Transformer\ObjectTransformerInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyUtilsBundle extends AbstractBundle
{
    public const MATH_CONFIG = [
        'format_decimal_separator' => ConfigParam::MathFormatDecimalSeparator,
        'format_thousands_separator' => ConfigParam::MathFormatThousandsSeparator,
        'round_mode' => ConfigParam::MathRoundMode,
        'round_precision' => ConfigParam::MathRoundPrecision,
        'scale' => ConfigParam::MathScale,
    ];

    public const SENSITIVE_DATA_DEFAULT_KEYS_TO_MASK = [
        'access_key',
        'access_secret',
        'access_token',
        'apikey',
        'auth_basic',
        'auth_bearer',
        'authorization',
        'card_number',
        'cert',
        'cvc',
        'cvv',
        'number',
        'password',
        'php-auth-pw',
        'php_auth_pw',
        'php-auth-user',
        'php_auth_user',
        'securitycode',
        'token',
        'verificationcode',
        'x-shared-key',
        '40309', // Value of the CURLOPT_CAINFO_BLOB constant
        '40291', // Value of the CURLOPT_SSLCERT_BLOB constant
        '40292', // Value of the CURLOPT_SSLKEY_BLOB constant
    ];

    private const STRING_TRIMMER_CONFIG = [
        'except_keys' => ConfigParam::StringTrimmerExceptKeys,
    ];

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
        foreach (self::MATH_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config['math'][$name]);
        }

        foreach (self::STRING_TRIMMER_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config['string_trimmer'][$name]);
        }

        $container->import('config/services.php');

        // SensitiveData
        if ($config['sensitive_data']['enabled'] ?? true) {
            $this->configureSensitiveDataSanitizer($config, $container, $builder);
        }

        // StringTrimmer
        if ($config['string_trimmer']['enabled'] ?? false) {
            $container->import('config/string_trimmer.php');
        }
    }

    private function configureSensitiveDataSanitizer(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $builder
            ->registerForAutoconfiguration(ObjectTransformerInterface::class)
            ->addTag(ConfigTag::SensitiveDataObjectTransformer->value);

        $builder
            ->registerForAutoconfiguration(StringSanitizerInterface::class)
            ->addTag(ConfigTag::SensitiveDataStringSanitizer->value);

        if ($config['sensitive_data']['use_default_object_transformers'] ?? true) {
            $container->import('config/sensitive_data_default_object_transformers.php');
        }

        if ($config['sensitive_data']['use_default_string_sanitizers'] ?? true) {
            $container->import('config/sensitive_data_default_string_sanitizers.php');
        }

        $container->import('config/sensitive_data.php');

        $defaultKeysToMask = ($config['sensitive_data']['use_default_keys_to_mask'] ?? true)
            ? self::SENSITIVE_DATA_DEFAULT_KEYS_TO_MASK
            : [];

        $keysToMask = $config['sensitive_data']['keys_to_mask'] ?? [];

        $container
            ->parameters()
            ->set(
                ConfigParam::SensitiveDataKeysToMask->value,
                \array_unique(\array_merge($defaultKeysToMask, $keysToMask))
            );

        $container
            ->parameters()
            ->set(
                ConfigParam::SensitiveDataMaskPattern->value,
                $config['sensitive_data']['mask_pattern'] ?? BundleParam::SensitiveDataDefaultMaskPattern->value
            );
    }
}
