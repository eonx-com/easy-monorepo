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

    public function __construct(ExpressionLanguageFactoryInterface $languageFactory)
    {
        $this->expressionLanguageFactory = $languageFactory;
    }

    public function create(DecisionConfigInterface $config): DecisionInterface
    {
        $decision = $this
            ->instantiateDecision($config->getDecisionType())
            ->setName($config->getName())
            ->setDefaultOutput($config->getDefaultOutput());

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

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    private function getExpressionLanguage(DecisionConfigInterface $config): ExpressionLanguageInterface
    {
        if ($this->expressionLanguage !== null) {
            return $this->expressionLanguage;
        }

        return $this->expressionLanguage = $this->expressionLanguageFactory->create(
            $config->getExpressionLanguageConfig() ?? new ExpressionLanguageConfig()
        );
    }

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
