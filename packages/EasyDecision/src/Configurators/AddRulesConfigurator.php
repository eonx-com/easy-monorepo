<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;

final class AddRulesConfigurator extends AbstractConfigurator
{
    /**
     * @var \EonX\EasyDecision\Interfaces\RuleInterface[]
     */
    private $rules;

    /**
     * @param \EonX\EasyDecision\Interfaces\RuleInterface[] $rules
     */
    public function __construct(array $rules, ?int $priority = null)
    {
        $this->rules = $rules;

        parent::__construct($priority);
    }

    public function configure(DecisionInterface $decision): void
    {
        $decision->addRules($this->rules);
    }
}
