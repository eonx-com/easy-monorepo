<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurator;

use EonX\EasyDecision\Decision\DecisionInterface;

final class SetNameDecisionConfigurator extends AbstractDecisionConfigurator
{
    public function __construct(
        private string $name,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(DecisionInterface $decision): void
    {
        $decision->setName($this->name);
    }
}
