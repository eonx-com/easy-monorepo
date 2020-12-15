<?php

declare(strict_types=1);

use EonX\EasyDecision\Configurators\SetExpressionLanguageConfigurator;
use EonX\EasyDecision\Expressions\ExpressionLanguage;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ExpressionLanguageInterface::class, ExpressionLanguage::class)
        ->factory([ref(ExpressionLanguageFactoryInterface::class), 'create']);

    $services
        ->set(SetExpressionLanguageConfigurator::class)
        ->arg('$priority', -5000);
};
