<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Tests\Stubs;

use LoyaltyCorp\EasyDecision\Bridge\Common\Interfaces\DecisionConfigProviderInterface;
use LoyaltyCorp\EasyDecision\Decisions\AffirmativeDecision;
use LoyaltyCorp\EasyDecision\Helpers\FromPhpExpressionFunctionProvider;
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

\class_alias(
    DecisionConfigProviderStub::class,
    'StepTheFkUp\EasyDecision\Tests\Stubs\DecisionConfigProviderStub',
    false
);
