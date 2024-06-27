<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stub\Provider;

use EonX\EasyDecision\Provider\RuleProviderInterface;
use EonX\EasyDecision\Rule\ExpressionLanguageRule;
use EonX\EasyDecision\Tests\Stub\Rule\RuleStub;

final class RuleProviderStub implements RuleProviderInterface
{
    /**
     * @return \EonX\EasyDecision\Rule\RuleInterface[]
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
