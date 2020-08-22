<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface ContextAggregatorInterface
{
    public function addContext(ContextInterface $context): self;

    /**
     * @return \EonX\EasyDecision\Interfaces\ContextInterface[]
     */
    public function getContexts(): array;
}
