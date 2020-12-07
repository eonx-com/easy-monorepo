<?php

declare(strict_types=1);

use EonX\EasyDecision\Tests\Bridge\Symfony\Stubs\Configurators\NameRestrictedExpressionFunctionConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_decision', [
        'use_expression_language' => true,
    ]);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(NameRestrictedExpressionFunctionConfigurator::class);
};
