<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Interfaces\DecisionInterface;

final class SetNameConfigurator extends AbstractConfigurator
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name, ?int $priority = null)
    {
        $this->name = $name;

        parent::__construct($priority);
    }

    public function configure(DecisionInterface $decision): void
    {
        $decision->setName($this->name);
    }
}
