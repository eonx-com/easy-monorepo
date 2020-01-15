<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface ContextAwareInterface
{
    /**
     * Set context.
     *
     * @param \EonX\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return void
     */
    public function setContext(ContextInterface $context): void;
}
