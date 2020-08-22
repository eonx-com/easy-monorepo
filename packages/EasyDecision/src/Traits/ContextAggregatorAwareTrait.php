<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Traits;

use EonX\EasyDecision\Interfaces\ContextAggregatorInterface;

trait ContextAggregatorAwareTrait
{
    /**
     * @var \EonX\EasyDecision\Interfaces\ContextAggregatorInterface
     */
    private $contextAggregator;

    public function setContextAggregator(ContextAggregatorInterface $contextAggregator): void
    {
        $this->contextAggregator = $contextAggregator;
    }
}
