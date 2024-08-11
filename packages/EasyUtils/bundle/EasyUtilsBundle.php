<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Bundle;

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
            ->set(ConfigParam::MathFormatDecimalSeparator->value, $config['math']['format_decimal_separator'])
            ->set(ConfigParam::MathFormatThousandsSeparator->value, $config['math']['format_thousands_separator'])
            ->set(ConfigParam::MathRoundMode->value, $config['math']['round_mode'])
            ->set(ConfigParam::MathRoundPrecision->value, $config['math']['round_precision'])
            ->set(ConfigParam::MathScale->value, $config['math']['scale']);

        $container->import('config/services.php');

        $this->registerSensitiveDataSanitizerConfiguration($config, $container, $builder);
        $this->registerStringTrimmerConfiguration($config, $container, $builder);
    }

    private function registerSensitiveDataSanitizerConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['sensitive_data_sanitizer'];

        if ($config['enabled'] === false) {
            return;
        }

        $builder
            ->registerForAutoconfiguration(ObjectTransformerInterface::class)
            ->addTag(ConfigTag::SensitiveDataObjectTransformer->value);

        $builder
            ->registerForAutoconfiguration(StringSanitizerInterface::class)
            ->addTag(ConfigTag::SensitiveDataStringSanitizer->value);

        $container
            ->parameters()
            ->set(
                ConfigParam::SensitiveDataKeysToMask->value,
                \array_unique(\array_merge(self::SENSITIVE_DATA_DEFAULT_KEYS_TO_MASK, $config['keys_to_mask']))
            )
            ->set(ConfigParam::SensitiveDataMaskPattern->value, $config['mask_pattern']);

        $container->import('config/sensitive_data.php');

        if ($config['use_default_object_transformers']) {
            $container->import('config/sensitive_data_default_object_transformers.php');
        }

        if ($config['use_default_string_sanitizers']) {
            $container->import('config/sensitive_data_default_string_sanitizers.php');
        }
    }

    private function registerStringTrimmerConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['string_trimmer']['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::StringTrimmerExceptKeys->value, $config['string_trimmer']['except_keys']);

        $container->import('config/string_trimmer.php');
    }
}
