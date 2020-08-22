<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface ContextAggregatorAwareInterface
{
    public function setContextAggregator(ContextAggregatorInterface $contextAggregator): void;
}
