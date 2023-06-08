<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\RestrictedRuleInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

final class AddRulesDecisionConfigurator extends AbstractConfigurator
{
    /**
     * @var iterable<\EonX\EasyDecision\Interfaces\RuleInterface>
     */
    private $rules;

    /**
     * @param mixed[]|iterable<\EonX\EasyDecision\Interfaces\RuleInterface> $rules
     */
    public function __construct(iterable $rules, ?int $priority = null)
    {
        parent::__construct($priority);

        $this->rules = CollectorHelper::filterByClassAsArray($rules, RuleInterface::class);
    }

    public function configure(DecisionInterface $decision): void
    {
        foreach ($this->rules as $rule) {
            if (($rule instanceof RestrictedRuleInterface) === false || $rule->supportsDecision($decision)) {
                $decision->addRule($rule);
            }
        }
    }
}
