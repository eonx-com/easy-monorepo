<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Decisions;

use StepTheFkUp\EasyDecision\Interfaces\DecisionConfigInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

final class DecisionConfig implements DecisionConfigInterface
{
    /**
     * @var string
     */
    private $decisionType;

    /**
     * @var null|\StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    private $expressionLanguageConfig;

    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    private $ruleProviders;

    /**
     * DecisionConfig constructor.
     *
     * @param string $decisionType
     * @param \StepTheFkUp\EasyDecision\Interfaces\RuleProviderInterface[] $ruleProviders
     * @param null|\StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface $config
     */
    public function __construct(
        string $decisionType,
        array $ruleProviders,
        ?ExpressionLanguageConfigInterface $config = null
    ) {
        $this->decisionType = $decisionType;
        $this->ruleProviders = $ruleProviders;
        $this->expressionLanguageConfig = $config;
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
     * @return null|\StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    public function getExpressionLanguageConfig(): ?ExpressionLanguageConfigInterface
    {
        return $this->expressionLanguageConfig;
    }

    /**
     * Get rules providers.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    public function getRuleProviders(): array
    {
        return $this->ruleProviders;
    }
}
