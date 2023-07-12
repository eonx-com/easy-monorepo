<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyUtils\Bridge\BridgeConstantsInterface;
use EonX\EasyUtils\Bridge\Symfony\EnvVarProcessors\ForBuildEnvVarProcessor;
use EonX\EasyUtils\CreditCard\CreditCardNumberValidator;
use EonX\EasyUtils\CreditCard\CreditCardNumberValidatorInterface;
use EonX\EasyUtils\Csv\CsvWithHeadersParser;
use EonX\EasyUtils\Csv\CsvWithHeadersParserInterface;
use EonX\EasyUtils\Interfaces\MathInterface;
use EonX\EasyUtils\Math\Math;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(CreditCardNumberValidatorInterface::class, CreditCardNumberValidator::class);
    $services->set(CsvWithHeadersParserInterface::class, CsvWithHeadersParser::class);
    $services->set(ForBuildEnvVarProcessor::class);

    $services->set(MathInterface::class, Math::class)
        ->arg('$roundPrecision', param(BridgeConstantsInterface::PARAM_MATH_ROUND_PRECISION))
        ->arg('$roundMode', param(BridgeConstantsInterface::PARAM_MATH_ROUND_MODE))
        ->arg('$scale', param(BridgeConstantsInterface::PARAM_MATH_SCALE))
        ->arg('$decimalSeparator', param(BridgeConstantsInterface::PARAM_MATH_FORMAT_DECIMAL_SEPARATOR))
        ->arg('$thousandsSeparator', param(BridgeConstantsInterface::PARAM_MATH_FORMAT_THOUSANDS_SEPARATOR));
};
