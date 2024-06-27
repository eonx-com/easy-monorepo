<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Bundle\Enum;

enum ConfigTag: string
{
    case DecisionConfigurator = 'easy_decision.decision_configurator';

    case DecisionRule = 'easy_decision.rule';
}
