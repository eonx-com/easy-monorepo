<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\RestrictedRuleInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;

final class AddRulesDecisionConfigurator extends AbstractConfigurator
{
    /**
     * @var mixed[]|iterable<\EonX\EasyDecision\Interfaces\RuleInterface>
     */
    private $rules;

    /**
     * @param mixed[]|iterable<\EonX\EasyDecision\Interfaces\RuleInterface> $rules
     */
    public function __construct(iterable $rules, ?int $priority = null)
    {
        parent::__construct($priority);

        $this->rules = $this->filterRules($rules);
    }

    public function configure(DecisionInterface $decision): void
    {
        foreach ($this->rules as $rule) {
            if (($rule instanceof RestrictedRuleInterface) === false || $rule->supportsDecision($decision)) {
                $decision->addRule($rule);
            }
        }
    }

    /**
     * @param mixed[]|iterable<\EonX\EasyDecision\Interfaces\RuleInterface> $rules
     *
     * @return \EonX\EasyDecision\Interfaces\RuleInterface[]
     */
    private function filterRules(iterable $rules): array
    {
        $rules = $rules instanceof \Traversable
            ? \iterator_to_array($rules)
            : (array)$rules;

        return \array_filter($rules, static function ($rule): bool {
            return $rule instanceof RuleInterface;
        });
    }
}
