<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Expressions;

use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

final class ExpressionLanguageConfig implements ExpressionLanguageConfigInterface
{
    /**
     * @var null|\Symfony\Component\ExpressionLanguage\ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var null|mixed[]
     */
    private $functions;

    /**
     * @var null|mixed[]
     */
    private $providers;

    /**
     * ExpressionLanguageConfig constructor.
     *
     * @param null|\Symfony\Component\ExpressionLanguage\ExpressionLanguage $expressionLanguage
     * @param null|mixed[] $providers
     * @param null|mixed[] $functions
     */
    public function __construct(
        ?BaseExpressionLanguage $expressionLanguage = null,
        ?array $providers = null,
        ?array $functions = null
    ) {
        $this->expressionLanguage = $expressionLanguage;
        $this->providers = $providers;
        $this->functions = $functions;
    }

    /**
     * Get base expression language instance.
     *
     * @return null|\Symfony\Component\ExpressionLanguage\ExpressionLanguage
     */
    public function getBaseExpressionLanguage(): ?BaseExpressionLanguage
    {
        return $this->expressionLanguage;
    }

    /**
     * Get function providers.
     *
     * @return null|mixed[]
     */
    public function getFunctionProviders(): ?array
    {
        return $this->providers;
    }

    /**
     * Get functions.
     *
     * @return null|mixed[]
     */
    public function getFunctions(): ?array
    {
        return $this->functions;
    }
}

\class_alias(
    ExpressionLanguageConfig::class,
    'StepTheFkUp\EasyDecision\Expressions\ExpressionLanguageConfig',
    false
);
