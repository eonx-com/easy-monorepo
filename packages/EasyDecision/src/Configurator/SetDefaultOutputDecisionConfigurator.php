<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurator;

use EonX\EasyDecision\Decision\DecisionInterface;

final class SetDefaultOutputDecisionConfigurator extends AbstractDecisionConfigurator
{
    public function __construct(
        private readonly mixed $defaultOutput,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(DecisionInterface $decision): void
    {
        $decision->setDefaultOutput($this->defaultOutput);
    }
}
