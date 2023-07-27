<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface DecisionConfiguratorInterface extends HasPriorityInterface
{
    public function configure(DecisionInterface $decision): void;
}
