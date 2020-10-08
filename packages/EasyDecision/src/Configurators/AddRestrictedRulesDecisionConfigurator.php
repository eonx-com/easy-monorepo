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
    private $restrictedRules;

    /**
     * @param iterable<\EonX\EasyDecision\Interfaces\RuleInterface> $restrictedRules
     */
    public function __construct(iterable $restrictedRules, ?int $priority = null)
    {
        parent::__construct($priority);

        $this->restrictedRules = $restrictedRules;
    }

    public function configure(DecisionInterface $decision): void
    {
        foreach ($this->restrictedRules as $rule) {
            if (($rule instanceof RestrictedRuleInterface) === false) {
                continue;
            }

            if ($rule->supportsDecision($decision) === true) {
                $decision->addRule($rule);
            }
        }
    }
}
