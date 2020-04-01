<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;

final class SetDefaultOutputConfigurator extends AbstractConfigurator
{
    /**
     * @var mixed
     */
    private $defaultOutput;

    /**
     * @param mixed $defaultOutput
     */
    public function __construct($defaultOutput, ?int $priority = null)
    {
        $this->defaultOutput = $defaultOutput;

        parent::__construct($priority);
    }

    public function configure(DecisionInterface $decision): void
    {
        $decision->setDefaultOutput($this->defaultOutput);
    }
}
