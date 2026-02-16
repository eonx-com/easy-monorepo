<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDecision\Tests\Stub\Configurator\NameRestrictedExpressionFunctionDecisionConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_decision', [
        'use_expression_language' => true,
    ]);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(NameRestrictedExpressionFunctionDecisionConfigurator::class);
};
