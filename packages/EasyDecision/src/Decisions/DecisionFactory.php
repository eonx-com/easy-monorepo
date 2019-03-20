<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Decisions;

use StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyDecision\Exceptions\InvalidDecisionException;
use StepTheFkUp\EasyDecision\Interfaces\DecisionConfigInterface;
use StepTheFkUp\EasyDecision\Interfaces\DecisionFactoryInterface;
use StepTheFkUp\EasyDecision\Interfaces\DecisionInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface;

final class DecisionFactory implements DecisionFactoryInterface
{
    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface
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
     * @param \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface $expressionLanguageFactory
     */
    public function __construct(array $mapping, ExpressionLanguageFactoryInterface $expressionLanguageFactory)
    {
        $this->mapping = $mapping;
        $this->expressionLanguageFactory = $expressionLanguageFactory;
    }

    /**
     * Create decision for given config.
     *
     * @param \StepTheFkUp\EasyDecision\Interfaces\DecisionConfigInterface $config
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(DecisionConfigInterface $config): DecisionInterface
    {
        $decision = $this->instantiateDecision($config->getDecisionType());

        if ($config->getExpressionLanguageConfig() !== null) {
            $decision->setExpressionLanguage($this->expressionLanguageFactory->create($config->getExpressionLanguageConfig()));
        }

        foreach ($config->getRuleProviders() as $provider) {
            $decision->addRules($provider->getRules());
        }

        return $decision;
    }

    /**
     * Create instance of decision for given type.
     *
     * @param string $decisionType
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     *
     * @throws \StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyDecision\Exceptions\InvalidDecisionException
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
