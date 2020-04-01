<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Common\Interfaces;

use EonX\EasyDecision\Interfaces\DecisionInterface;

/**
 * @deprecated since 2.3.7
 */
interface DecisionFactoryInterface
{
    /**
     * @param null|mixed[] $params
     */
    public function create(string $decision, ?array $params = null): DecisionInterface;
}
