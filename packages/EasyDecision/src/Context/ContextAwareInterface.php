<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Context;

interface ContextAwareInterface
{
    public function setContext(ContextInterface $context): void;
}
