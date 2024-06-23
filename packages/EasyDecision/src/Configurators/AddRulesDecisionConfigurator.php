<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\RestrictedRuleInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;

final class AddRulesDecisionConfigurator extends AbstractConfigurator
{
    /**
     * @var \EonX\EasyDecision\Interfaces\RuleInterface[]
     */
    private array $rules;

    public function __construct(iterable $rules, ?int $priority = null)
    {
        parent::__construct($priority);

        /** @var \EonX\EasyDecision\Interfaces\RuleInterface[] $filteredRules */
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
