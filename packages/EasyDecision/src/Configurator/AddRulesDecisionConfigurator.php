<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurator;

use EonX\EasyDecision\Decision\DecisionInterface;
use EonX\EasyDecision\Rule\RestrictedRuleInterface;
use EonX\EasyDecision\Rule\RuleInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

final class AddRulesDecisionConfigurator extends AbstractDecisionConfigurator
{
    /**
     * @var \EonX\EasyDecision\Rule\RuleInterface[]
     */
    private array $rules;

    public function __construct(iterable $rules, ?int $priority = null)
    {
        parent::__construct($priority);

        /** @var \EonX\EasyDecision\Rule\RuleInterface[] $filteredRules */
        $filteredRules = CollectorHelper::filterByClassAsArray($rules, RuleInterface::class);
        $this->rules = $filteredRules;
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
