<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyUtils\Bundle\Enum\ConfigParam;
use EonX\EasyUtils\Common\EnvVarProcessor\ForBuildEnvVarProcessor;
use EonX\EasyUtils\Common\EnvVarProcessor\NullableEnumEnvVarProcessor;
use EonX\EasyUtils\CreditCard\Validator\CreditCardNumberValidator;
use EonX\EasyUtils\CreditCard\Validator\CreditCardNumberValidatorInterface;
use EonX\EasyUtils\Csv\Parser\CsvWithHeadersParser;
use EonX\EasyUtils\Csv\Parser\CsvWithHeadersParserInterface;
use EonX\EasyUtils\Math\Helper\MathHelper;
use EonX\EasyUtils\Math\Helper\MathHelperInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(CreditCardNumberValidatorInterface::class, CreditCardNumberValidator::class);
    $services->set(CsvWithHeadersParserInterface::class, CsvWithHeadersParser::class);

    $services->set(ForBuildEnvVarProcessor::class);
    $services->set(NullableEnumEnvVarProcessor::class);

    $services->set(MathHelperInterface::class, MathHelper::class)
        ->arg('$roundPrecision', param(ConfigParam::MathRoundPrecision->value))
        ->arg('$roundMode', param(ConfigParam::MathRoundMode->value))
        ->arg('$scale', param(ConfigParam::MathScale->value))
        ->arg('$decimalSeparator', param(ConfigParam::MathFormatDecimalSeparator->value))
        ->arg('$thousandsSeparator', param(ConfigParam::MathFormatThousandsSeparator->value));
};
