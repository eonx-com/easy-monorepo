<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Interfaces\DecisionConfigInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

final class DecisionConfig implements DecisionConfigInterface
{
    /**
     * @var string
     */
    private $decisionType;

    /**
     * @var null|\EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    private $expressionLanguageConfig;

    /**
     * @var string
     */
    private $name;

    /**
     * @var null|mixed[]
     */
    private $params;

    /**
     * @var \EonX\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    private $ruleProviders;

    /**
     * DecisionConfig constructor.
     *
     * @param string $decisionType
     * @param string $name
     * @param \EonX\EasyDecision\Interfaces\RuleProviderInterface[] $ruleProviders
     * @param null|\EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface $config
     * @param null|mixed[] $params
     */
    public function __construct(
        string $decisionType,
        string $name,
        array $ruleProviders,
        ?ExpressionLanguageConfigInterface $config = null,
        ?array $params = null
    ) {
        $this->decisionType = $decisionType;
        $this->name = $name;
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
     * @return null|\EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     */
    public function getExpressionLanguageConfig(): ?ExpressionLanguageConfigInterface
    {
        return $this->expressionLanguageConfig;
    }

    /**
     * Get decision name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * @return \EonX\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    public function getRuleProviders(): array
    {
        return $this->ruleProviders;
    }
}
