<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Decisions;

use EonX\EasyDecision\Configurators\AddRulesConfigurator;
use EonX\EasyDecision\Configurators\SetDefaultOutputConfigurator;
use EonX\EasyDecision\Configurators\SetExpressionLanguageConfigurator;
use EonX\EasyDecision\Configurators\SetNameConfigurator;
use EonX\EasyDecision\Exceptions\InvalidDecisionException;
use EonX\EasyDecision\Exceptions\InvalidRuleProviderException;
use EonX\EasyDecision\Expressions\ExpressionLanguageFactory;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Interfaces\DecisionAggregatorInterface;
use EonX\EasyDecision\Interfaces\DecisionConfigInterface;
use EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface;
use EonX\EasyDecision\Interfaces\DecisionFactoryInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\MappingProviderInterface;
use EonX\EasyDecision\Interfaces\RestrictedDecisionConfiguratorInterface;
use EonX\EasyDecision\Interfaces\RuleProviderInterface;
use Psr\Container\ContainerInterface;

final class DecisionFactory implements DecisionFactoryInterface
{
    /**
     * @var \EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var \EonX\EasyDecision\Interfaces\DecisionAggregatorInterface
     */
    private $decisionAggregator;

    /**
     * @var null|\EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageFactoryInterface
     */
    private $expressionLanguageFactory;

    /**
     * @var \EonX\EasyDecision\Interfaces\MappingProviderInterface
     */
    private $mappingProvider;

    /**
     * @param null|iterable<mixed> $configurators
     */
    public function __construct(
        DecisionAggregatorInterface $decisionAggregator,
        MappingProviderInterface $mappingProvider,
        ?ExpressionLanguageFactoryInterface $languageFactory = null,
        ?iterable $configurators = null
    ) {
        if ($languageFactory !== null) {
            @\trigger_error(\sprintf(
                'Passing %s in %s constructor is deprecated since 2.3.7 and will be removed in 3.0. Use a %s instead',
                ExpressionLanguageFactoryInterface::class,
                static::class,
                DecisionConfiguratorInterface::class
            ), \E_USER_DEPRECATED);
        }

        $this->decisionAggregator = $decisionAggregator;
        $this->mappingProvider = $mappingProvider;
        $this->expressionLanguageFactory = $languageFactory;
        $this->configurators = $this->getConfiguratorsAsArray($configurators);
    }

    /**
     * @deprecated since 2.3.7
     */
    public function create(DecisionConfigInterface $config): DecisionInterface
    {
        @\trigger_error(\sprintf(
            '%s::%s() is deprecated since 2.3.7 and will be removed in 3.0, use one of %s methods instead',
            static::class,
            __METHOD__,
            \implode(', ', [
                'createAffirmativeDecision',
                'createConsensusDecision',
                'createUnanimousDecision',
                'createValueDecision',
            ])
        ), \E_USER_DEPRECATED);

        $decision = $this->instantiateDecision($config->getDecisionType());

        $this->configurators[] = new SetNameConfigurator($config->getName());
        $this->configurators[] = new SetDefaultOutputConfigurator($config->getDefaultOutput());
        $this->configurators[] = new SetExpressionLanguageConfigurator(
            $this->expressionLanguageFactory ?? new ExpressionLanguageFactory()
        );
        $params = $config->getParams();

        foreach ($config->getRuleProviders() as $provider) {
            if (($provider instanceof RuleProviderInterface) === false) {
                throw new InvalidRuleProviderException(\sprintf(
                    'RuleProvider "%s" does not implement %s',
                    \get_class($provider),
                    RuleProviderInterface::class
                ));
            }

            $this->configurators[] = new AddRulesConfigurator($provider->getRules($params));
        }

        $this->decisionAggregator->addDecisionRuleProviders($decision, $config->getRuleProviders());

        return $this->configureDecision($decision);
    }

    public function createAffirmativeDecision(?string $name = null): DecisionInterface
    {
        return $this->configureDecision(new AffirmativeDecision($name));
    }

    public function createByName(string $name): DecisionInterface
    {
        $decision = $this->mappingProvider->getDecisionType($name);

        return $this->configureDecision(new $decision($name));
    }

    public function createConsensusDecision(?string $name = null): DecisionInterface
    {
        return $this->configureDecision(new ConsensusDecision($name));
    }

    public function createUnanimousDecision(?string $name = null): DecisionInterface
    {
        return $this->configureDecision(new UnanimousDecision($name));
    }

    public function createValueDecision(?string $name = null): DecisionInterface
    {
        return $this->configureDecision(new ValueDecision($name));
    }

    /**
     * @deprecated since 2.3.7
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    private function configureDecision(DecisionInterface $decision): DecisionInterface
    {
        // Sort configurators by priority
        $configurators = $this->configurators;

        \usort(
            $configurators,
            static function (DecisionConfiguratorInterface $first, DecisionConfiguratorInterface $second): int {
                return $first->getPriority() <=> $second->getPriority();
            }
        );

        foreach ($configurators as $configurator) {
            if ($configurator instanceof RestrictedDecisionConfiguratorInterface
                && $configurator->supports($decision) === false) {
                continue;
            }

            $configurator->configure($decision);
        }

        $this->decisionAggregator->addDecision($decision);
        $this->decisionAggregator->addDecisionConfigurators($decision, $configurators);

        return $decision;
    }

    /**
     * @param null|iterable<mixed> $configurators
     *
     * @return \EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface[]
     */
    private function getConfiguratorsAsArray(?iterable $configurators = null): array
    {
        if ($configurators === null) {
            return [];
        }

        if ($configurators instanceof \Traversable) {
            $configurators = \iterator_to_array($configurators);
        }

        $filter = static function ($item): bool {
            return $item instanceof DecisionConfiguratorInterface;
        };

        return \array_filter($configurators, $filter);
    }

    /**
     * @deprecated since 2.3.7
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
