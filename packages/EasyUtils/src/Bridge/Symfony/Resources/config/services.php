<?php

declare(strict_types=1);

use EonX\EasyUtils\Bridge\BridgeConstantsInterface;
use EonX\EasyUtils\Interfaces\MathInterface;
use EonX\EasyUtils\Math;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(MathInterface::class, Math::class)
        ->arg('$roundPrecision', '%' . BridgeConstantsInterface::PARAM_MATH_ROUND_PRECISION . '%')
        ->arg('$roundMode', '%' . BridgeConstantsInterface::PARAM_MATH_ROUND_MODE . '%')
        ->arg('$scale', '%' . BridgeConstantsInterface::PARAM_MATH_SCALE . '%')
        ->arg('$decimalSeparator', '%' . BridgeConstantsInterface::PARAM_MATH_FORMAT_DECIMAL_SEPARATOR . '%')
        ->arg('$thousandsSeparator', '%' . BridgeConstantsInterface::PARAM_MATH_FORMAT_THOUSANDS_SEPARATOR . '%');
};
