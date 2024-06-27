<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDecision\Configurator\SetExpressionLanguageDecisionConfigurator;
use EonX\EasyDecision\ExpressionLanguage\ExpressionLanguage;
use EonX\EasyDecision\ExpressionLanguage\ExpressionLanguageInterface;
use EonX\EasyDecision\Factory\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Provider\ValueExpressionFunctionProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ExpressionLanguageInterface::class, ExpressionLanguage::class)
        ->factory([service(ExpressionLanguageFactoryInterface::class), 'create']);

    $services
        ->set(SetExpressionLanguageDecisionConfigurator::class)
        ->arg('$priority', -5000);

    $services->set(ValueExpressionFunctionProvider::class);
};
