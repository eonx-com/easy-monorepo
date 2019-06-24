<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Decisions;

use LoyaltyCorp\EasyDecision\Interfaces\DecisionConfigInterface;
use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

final class DecisionConfig implements DecisionConfigInterface
{
    /**
     * @var string
     */
    private $decisionType;

    /**
     * @var null|\LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    private $expressionLanguageConfig;

    /**
     * @var null|mixed[]
     */
    private $params;

    /**
     * @var \LoyaltyCorp\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    private $ruleProviders;

    /**
     * DecisionConfig constructor.
     *
     * @param string $decisionType
     * @param \LoyaltyCorp\EasyDecision\Interfaces\RuleProviderInterface[] $ruleProviders
     * @param null|\LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface $config
     * @param null|mixed[]
     */
    public function __construct(
        string $decisionType,
        array $ruleProviders,
        ?ExpressionLanguageConfigInterface $config = null,
        ?array $params = null
    ) {
        $this->decisionType = $decisionType;
        $this->ruleProviders = $ruleProviders;
        $this->expressionLanguageConfig = $config;
        $this->params = $params;
    }

    /**
     * Get decision type.
     *
     * @return string
     */
    public function getDecisionType(): string
    {
        return $this->decisionType;
    }

    /**
     * Get expression language config.
     *
     * @return null|\LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    public function getExpressionLanguageConfig(): ?ExpressionLanguageConfigInterface
    {
        return $this->expressionLanguageConfig;
    }

    /**
     * Get additional params.
     *
     * @return null|mixed[]
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * Get rules providers.
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    public function getRuleProviders(): array
    {
        return $this->ruleProviders;
    }
}

\class_alias(
    DecisionConfig::class,
    'StepTheFkUp\EasyDecision\Decisions\DecisionConfig',
    false
);
