<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Exceptions\InvalidDecisionException;
use EonX\EasyDecision\Exceptions\InvalidRuleProviderException;
use EonX\EasyDecision\Expressions\ExpressionLanguageConfig;
use EonX\EasyDecision\Interfaces\DecisionConfigInterface;
use EonX\EasyDecision\Interfaces\DecisionFactoryInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\ExpressionLanguageAwareInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface;
use EonX\EasyDecision\Interfaces\RuleProviderInterface;
use Psr\Container\ContainerInterface;

final class DecisionFactory implements DecisionFactoryInterface
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
     */
    private $expressionLanguage;

    /**
     * @var \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface
     */
    private $expressionLanguageFactory;

    /**
     * DecisionFactory constructor.
     *
     * @param \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface $languageFactory
     */
    public function __construct(ExpressionLanguageFactoryInterface $languageFactory)
    {
        $this->expressionLanguageFactory = $languageFactory;
    }

    /**
     * Create decision for given config.
     *
     * @param \EonX\EasyDecision\Interfaces\DecisionConfigInterface $config
     *
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(DecisionConfigInterface $config): DecisionInterface
    {
        $decision = $this->instantiateDecision($config->getDecisionType())->setName($config->getName());

        foreach ($config->getRuleProviders() as $provider) {
            if (($provider instanceof RuleProviderInterface) === false) {
                throw new InvalidRuleProviderException(\sprintf(
                    'RuleProvider "%s" does not implement %s',
                    \get_class($provider),
                    RuleProviderInterface::class
                ));
            }

            $params = $config->getParams();

            foreach ($provider->getRules($params) as $rule) {
                if ($rule instanceof ExpressionLanguageAwareInterface) {
                    $rule->setExpressionLanguage($this->getExpressionLanguage($config));
                }

                $decision->addRule($rule);
            }
        }

        return $decision;
    }

    /**
     * Set container.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Get expression language for given config.
     *
     * @param \EonX\EasyDecision\Interfaces\DecisionConfigInterface $config
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageInterface
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
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface
     *
     * @throws \EonX\EasyDecision\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyDecision\Exceptions\InvalidDecisionException
     */
    private function instantiateDecision(string $decisionType): DecisionInterface
    {
        $decision = null;

        try {
            if ($this->container !== null && $this->container->has($decisionType)) {
                $decision = $this->container->get($decisionType);
            }

            if ($decision === null && \class_exists($decisionType)) {
                $decision = new $decisionType();
            }
        } catch (\Throwable $exception) {
            throw new InvalidDecisionException(\sprintf('Unable to instantiate decision for type "%s"', $decisionType));
        }

        if ($decision instanceof DecisionInterface) {
            return $decision;
        }

        throw new InvalidDecisionException(\sprintf(
            'Configured decision "%s" does not implement %s',
            $decisionType,
            DecisionInterface::class
        ));
    }
}
