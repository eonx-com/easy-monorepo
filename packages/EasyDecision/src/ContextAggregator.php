<?php
declare(strict_types=1);

namespace EonX\EasyDecision;

use EonX\EasyDecision\Interfaces\ContextAggregatorInterface;
use EonX\EasyDecision\Interfaces\ContextInterface;

final class ContextAggregator implements ContextAggregatorInterface
{
    /**
     * @var \EonX\EasyDecision\Interfaces\ContextInterface[]
     */
    private $contexts = [];

    public function addContext(ContextInterface $context): ContextAggregatorInterface
    {
        $this->contexts[] = $context;

        return $this;
    }

    public function getContexts(): array
    {
        return $this->contexts;
    }
}
