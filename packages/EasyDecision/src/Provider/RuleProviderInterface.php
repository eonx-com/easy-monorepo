<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Provider;

interface RuleProviderInterface
{
    /**
     * @return \EonX\EasyDecision\Rule\RuleInterface[]
     */
    public function getRules(?array $params = null): array;
}
