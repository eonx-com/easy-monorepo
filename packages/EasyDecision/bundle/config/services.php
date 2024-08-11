<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDecision\Bundle\Enum\ConfigTag;
use EonX\EasyDecision\Configurator\AddRulesDecisionConfigurator;
use EonX\EasyDecision\DataCollector\DecisionDataCollector;
use EonX\EasyDecision\Factory\DecisionFactory;
use EonX\EasyDecision\Factory\DecisionFactoryInterface;
use EonX\EasyDecision\Factory\ExpressionLanguageFactory;
use EonX\EasyDecision\Factory\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Factory\ExpressionLanguageRuleFactory;
use EonX\EasyDecision\Factory\ExpressionLanguageRuleFactoryInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(AddRulesDecisionConfigurator::class)
        ->arg('$rules', tagged_iterator(ConfigTag::DecisionRule->value));

    $services->set(DecisionFactoryInterface::class, DecisionFactory::class)
        ->arg('$configurators', tagged_iterator(ConfigTag::DecisionConfigurator->value));

    $services->set(ExpressionLanguageFactoryInterface::class, ExpressionLanguageFactory::class);

    $services->set(ExpressionLanguageRuleFactoryInterface::class, ExpressionLanguageRuleFactory::class);

    $services->set(DecisionDataCollector::class);
};
