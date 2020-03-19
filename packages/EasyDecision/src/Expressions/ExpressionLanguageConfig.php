<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Expressions;

use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;
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

    public function getBaseExpressionLanguage(): ?BaseExpressionLanguage
    {
        return $this->expressionLanguage;
    }

    /**
     * @return null|mixed[]
     */
    public function getFunctionProviders(): ?array
    {
        return $this->providers;
    }

    /**
     * @return null|mixed[]
     */
    public function getFunctions(): ?array
    {
        return $this->functions;
    }
}
