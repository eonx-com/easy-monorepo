<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Common\Interfaces;

use EonX\EasyDecision\Interfaces\DecisionInterface;

interface DecisionFactoryInterface
{
    /**
     * Create decision for given decision name.
     *
     * @param string $decision
     * @param null|mixed[] $params
     *
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(string $decision, ?array $params = null): DecisionInterface;
}
