<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;

final class AddRulesConfigurator extends AbstractConfigurator
{
    /**
     * @param \EonX\EasyDecision\Interfaces\RuleInterface[] $rules
     */
    public function __construct(
        private array $rules,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(DecisionInterface $decision): void
    {
        $decision->addRules($this->rules);
    }
}
