<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Bridge\Common\Interfaces;

use LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface;

interface DecisionFactoryInterface
{
    /**
     * Create decision for given decision name.
     *
     * @param string $decision
     * @param null|mixed[] $params
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(string $decision, ?array $params = null): DecisionInterface;
}
