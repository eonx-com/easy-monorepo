<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Stubs;

use EonX\EasyDecision\Bridge\Common\Interfaces\DecisionConfigProviderInterface;
use EonX\EasyDecision\Decisions\AffirmativeDecision;
use EonX\EasyDecision\Helpers\FromPhpExpressionFunctionProvider;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final class DecisionConfigProviderStub implements DecisionConfigProviderInterface
{
    public function getDecisionType(): string
    {
        return AffirmativeDecision::class;
    }

    /**
     * @return null|mixed
     */
    public function getDefaultOutput()
    {
        return null;
    }

    /**
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
     * @return null|mixed[]
     */
    public function getExpressionFunctions(): ?array
    {
        return [
            ExpressionFunction::fromPhp('is_array')
        ];
    }

    /**
     * @return mixed[]
     */
    public function getRuleProviders(): array
    {
        return [
            new RuleProviderStub()
        ];
    }
}
