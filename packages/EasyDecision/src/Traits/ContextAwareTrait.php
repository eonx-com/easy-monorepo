<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Traits;

use EonX\EasyDecision\Interfaces\ContextInterface;

trait ContextAwareTrait
{
    /**
     * @var \EonX\EasyDecision\Interfaces\ContextInterface
     */
    private $context;

    public function setContext(ContextInterface $context): void
    {
        $this->context = $context;
    }
}
