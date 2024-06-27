<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDecision\Tests\Stub\Configurator\TypeRestrictedExpressionFunctionDecisionConfigurator;
use Symfony\Config\EasyDecisionConfig;

return static function (EasyDecisionConfig $easyDecisionConfig, ContainerConfigurator $containerConfigurator): void {
    $easyDecisionConfig->useExpressionLanguage(true);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(TypeRestrictedExpressionFunctionDecisionConfigurator::class);
};
