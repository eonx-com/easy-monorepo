<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Tests\Stubs;

use StepTheFkUp\EasyDecision\Bridge\Laravel\DecisionConfigProviderInterface;
use StepTheFkUp\EasyDecision\Helpers\FromPhpExpressionFunctionProvider;
use StepTheFkUp\EasyDecision\Interfaces\DecisionInterface;
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
        return DecisionInterface::TYPE_YESNO_AFFIRMATIVE;
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
