<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Interfaces\RuleProviderInterface;
use EonX\EasyDecision\Rules\ExpressionLanguageRule;

final class RuleProviderStub implements RuleProviderInterface
{
    /**
     * @return \EonX\EasyDecision\Interfaces\RuleInterface[]
     */
    public function getRules(?array $params = null): array
    {
        return [
            new RuleStub('true-1', true),
            new ExpressionLanguageRule('value === 1'),
            new ExpressionLanguageRule('value < 2'),
        ];
    }
}
