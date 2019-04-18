<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Traits;

use StepTheFkUp\EasyDecision\Exceptions\ContextNotSetException;
use StepTheFkUp\EasyDecision\Interfaces\ContextInterface;

trait ContextAwareTrait
{
    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\ContextInterface
     */
    private $context;

    /**
     * Get context.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\ContextInterface
     *
     * @throws \StepTheFkUp\EasyDecision\Exceptions\ContextNotSetException
     */
    public function getContext(): ContextInterface
    {
        if ($this->context !== null) {
            return $this->context;
        }

        throw new ContextNotSetException(\sprintf(
            'In "%s" context not set, you cannot called getContext()',
            \get_class($this)
        ));
    }

    /**
     * Set context.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\ContextInterface $context
     *
     * @return void
     */
    public function setContext(ContextInterface $context): void
    {
        $this->context = $context;
    }
}

\class_alias(
    ContextAwareTrait::class,
    'LoyaltyCorp\EasyDecision\Traits\ContextAwareTrait',
    false
);
