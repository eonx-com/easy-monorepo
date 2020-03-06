<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface ContextAwareInterface
{
    public function setContext(ContextInterface $context): void;
}
