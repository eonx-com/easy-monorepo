<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Traits;

use EonX\EasyDecision\Interfaces\DecisionContextInterface;

trait ContextAwareTrait
{
    /**
     * @var \EonX\EasyDecision\Interfaces\DecisionContextInterface
     */
    private $context;

    public function setContext(DecisionContextInterface $context): void
    {
        $this->context = $context;
    }
}
