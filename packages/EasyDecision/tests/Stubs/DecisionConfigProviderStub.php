<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Bridge\Common\Interfaces\DecisionConfigProviderInterface;
use EonX\EasyDecision\Decisions\AffirmativeDecision;
use EonX\EasyDecision\Helpers\FromPhpExpressionFunctionProvider;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final class DecisionConfigProviderStub implements DecisionConfigProviderInterface
{
    /**
     * Get decision type.
     *
     * @return string
     */
    public function getDecisionType(): string
    {
        return AffirmativeDecision::class;
    }

    /**
     * Get expression functions providers list.
     *
     * @return null|mixed[]
     */
    public function getExpressionFunctionProviders(): ?array
    {
        return [
            new FromPhpExpressionFunctionProvider(['max']),
            'minPhpFunctionProvider'
        ];
    }

    /**
     * Get expression functions list.
     *
     * @return null|mixed[]
     */
    public function getExpressionFunctions(): ?array
    {
        return [
            ExpressionFunction::fromPhp('is_array')
        ];
    }

    /**
     * Get rule providers. Can be an instance or service locator.
     *
     * @return mixed[]
     */
    public function getRuleProviders(): array
    {
        return [
            new RuleProviderStub()
        ];
    }
}


