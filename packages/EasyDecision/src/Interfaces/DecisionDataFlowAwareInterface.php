<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use EonX\EasyDecision\Data\DecisionDataFlow;

interface DecisionDataFlowAwareInterface
{
    public function getDataFlow(): DecisionDataFlow;
}
