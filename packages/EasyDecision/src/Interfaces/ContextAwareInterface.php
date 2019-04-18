<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Interfaces;

interface ContextAwareInterface
{
    /**
     * Get context.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\ContextInterface
     *
     * @throws \StepTheFkUp\EasyDecision\Exceptions\ContextNotSetException
     */
    public function getContext(): ContextInterface;

    /**
     * Set context.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return void
     */
    public function setContext(ContextInterface $context): void;
}

\class_alias(
    ContextAwareInterface::class,
    'LoyaltyCorp\EasyDecision\Interfaces\ContextAwareInterface',
    false
);
