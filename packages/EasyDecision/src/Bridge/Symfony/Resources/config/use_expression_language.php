<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDecision\Configurators\SetExpressionLanguageConfigurator;
use EonX\EasyDecision\Expressions\ExpressionLanguage;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;
use EonX\EasyDecision\Helpers\ValueExpressionFunctionProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ExpressionLanguageInterface::class, ExpressionLanguage::class)
        ->factory([service(ExpressionLanguageFactoryInterface::class), 'create']);

    $services
        ->set(SetExpressionLanguageConfigurator::class)
        ->arg('$priority', -5000);

    $services->set(ValueExpressionFunctionProvider::class);
};
