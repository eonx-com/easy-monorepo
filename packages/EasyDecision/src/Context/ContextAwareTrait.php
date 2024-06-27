<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Context;

trait ContextAwareTrait
{
    private ContextInterface $context;

    public function setContext(ContextInterface $context): void
    {
        $this->context = $context;
    }
}
