<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\RestrictedRuleInterface;

final class AddRestrictedRulesDecisionConfigurator extends AbstractConfigurator
{
    /**
     * @var iterable<\EonX\EasyDecision\Interfaces\RuleInterface>
     */
    private $rules;

    /**
     * @param iterable<\EonX\EasyDecision\Interfaces\RuleInterface> $rules
     */
    public function __construct(iterable $rules, ?int $priority = null)
    {
        parent::__construct($priority);

        $this->rules = $rules;
    }

    public function configure(DecisionInterface $decision): void
    {
        foreach ($this->rules as $rule) {
            if (($rule instanceof RestrictedRuleInterface) === false) {
                $decision->addRule($rule);

                continue;
            }

            if ($rule->supportsDecision($decision) === true) {
                $decision->addRule($rule);
            }
        }
    }
}
