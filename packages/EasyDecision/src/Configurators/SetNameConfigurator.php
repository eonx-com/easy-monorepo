<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;

final class SetNameConfigurator extends AbstractConfigurator
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
