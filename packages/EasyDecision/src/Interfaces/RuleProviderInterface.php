<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

interface RuleProviderInterface
{
    /**
     * @return \EonX\EasyDecision\Interfaces\RuleInterface[]
     */
    public function getRules(?array $params = null): array;
}
