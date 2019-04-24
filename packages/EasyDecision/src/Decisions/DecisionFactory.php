<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Decisions;

use LoyaltyCorp\EasyDecision\Exceptions\InvalidArgumentException;
use LoyaltyCorp\EasyDecision\Exceptions\InvalidDecisionException;
use LoyaltyCorp\EasyDecision\Exceptions\InvalidRuleProviderException;
use LoyaltyCorp\EasyDecision\Expressions\ExpressionLanguageConfig;
use LoyaltyCorp\EasyDecision\Interfaces\DecisionConfigInterface;
use LoyaltyCorp\EasyDecision\Interfaces\DecisionFactoryInterface;
use LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface;
use LoyaltyCorp\EasyDecision\Interfaces\ExpressionLanguageAwareInterface;
use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface;
use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;
use LoyaltyCorp\EasyDecision\Interfaces\RuleProviderInterface;

final class DecisionFactory implements DecisionFactoryInterface
{
    /**
     * @var \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
     */
    private $expressionLanguage;

    /**
     * @var \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface
     */
    private $expressionLanguageFactory;

    /**
     * @var string[]
     */
    private $mapping;

    /**
     * DecisionFactory constructor.
     *
     * @param string[] $mapping
     * @param \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface $languageFactory
     */
    public function __construct(array $mapping, ExpressionLanguageFactoryInterface $languageFactory)
    {
        $this->mapping = $mapping;
        $this->expressionLanguageFactory = $languageFactory;
    }

    /**
     * Create decision for given config.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\DecisionConfigInterface $config
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(DecisionConfigInterface $config): DecisionInterface
    {
        $decision = $this->instantiateDecision($config->getDecisionType());

        foreach ($config->getRuleProviders() as $provider) {
            if (($provider instanceof RuleProviderInterface) === false) {
                throw new InvalidRuleProviderException(\sprintf(
                    'RuleProvider "%s" does not implement %s',
                    \get_class($provider),
                    RuleProviderInterface::class
                ));
            }

            foreach ($provider->getRules() as $rule) {
                if ($rule instanceof ExpressionLanguageAwareInterface) {
                    $rule->setExpressionLanguage($this->getExpressionLanguage($config));
                }

                $decision->addRule($rule);
            }
        }

        return $decision;
    }

    /**
     * Get expression language for given config.
     *
     * @param \LoyaltyCorp\EasyDecision\Interfaces\DecisionConfigInterface $config
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
     */
    private function getExpressionLanguage(DecisionConfigInterface $config): ExpressionLanguageInterface
    {
        if ($this->expressionLanguage !== null) {
            return $this->expressionLanguage;
        }

        return $this->expressionLanguage = $this->expressionLanguageFactory->create(
            $config->getExpressionLanguageConfig() ?? new ExpressionLanguageConfig()
        );
    }

    /**
     * Create instance of decision for given type.
     *
     * @param string $decisionType
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     *
     * @throws \LoyaltyCorp\EasyDecision\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyDecision\Exceptions\InvalidDecisionException
     */
    private function instantiateDecision(string $decisionType): DecisionInterface
    {
        if (empty($this->mapping[$decisionType])) {
            throw new InvalidArgumentException(\sprintf('No decision class configured for type "%s"', $decisionType));
        }

        $class = $this->mapping[$decisionType];
        $decision = new $class();

        if ($decision instanceof DecisionInterface) {
            return $decision;
        }

        throw new InvalidDecisionException(\sprintf(
            'Configured decision "%s" for type "%s" does not implement %s',
            \get_class($decision),
            $decisionType,
            DecisionInterface::class
        ));
    }
}

\class_alias(
    DecisionFactory::class,
    'StepTheFkUp\EasyDecision\Decisions\DecisionFactory',
    false
);
