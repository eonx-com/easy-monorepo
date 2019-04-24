<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Interfaces;

interface ContextAwareInterface
{
    /**
     * Get context.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface
     *
     * @throws \LoyaltyCorp\EasyDecision\Exceptions\ContextNotSetException
     */
    public function getContext(): ContextInterface;

    /**
     * Set context.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return void
     */
    public function setContext(ContextInterface $context): void;
}

\class_alias(
    ContextAwareInterface::class,
    'StepTheFkUp\EasyDecision\Interfaces\ContextAwareInterface',
    false
);
