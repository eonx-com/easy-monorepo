<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Traits;

use LoyaltyCorp\EasyDecision\Interfaces\ContextInterface;

trait ContextAwareTrait
{
    /**
     * @var \LoyaltyCorp\EasyDecision\Interfaces\ContextInterface
     */
    private $context;

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


