<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;

final class SetDefaultOutputConfigurator extends AbstractConfigurator
{
    public function __construct(
        private mixed $defaultOutput,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(DecisionInterface $decision): void
    {
        $decision->setDefaultOutput($this->defaultOutput);
    }
}
