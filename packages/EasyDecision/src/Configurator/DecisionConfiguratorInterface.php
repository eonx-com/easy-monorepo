<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurator;

use EonX\EasyDecision\Decision\DecisionInterface;
use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface DecisionConfiguratorInterface extends HasPriorityInterface
{
    public function configure(DecisionInterface $decision): void;
}
