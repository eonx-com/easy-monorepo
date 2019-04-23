<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Traits;

use LoyaltyCorp\EasyDecision\Exceptions\ContextNotSetException;
use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;

trait ContextAwareTrait
{
    /**
     * @var \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface
     */
    private $context;

    /**
     * Get context.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface
     *
     * @throws \LoyaltyCorp\EasyDecision\Exceptions\ContextNotSetException
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
     * @param \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface $context
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
    'StepTheFkUp\EasyDecision\Traits\ContextAwareTrait',
    false
);
