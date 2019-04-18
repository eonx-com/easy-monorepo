<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Bridge\Laravel;

use Illuminate\Contracts\Container\Container;
use StepTheFkUp\EasyDecision\Decisions\DecisionConfig;
use StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyDecision\Interfaces\DecisionFactoryInterface as BaseDecisionFactoryInterface;
use StepTheFkUp\EasyDecision\Interfaces\DecisionInterface;
use StepTheFkUp\EasyDecision\Interfaces\RuleProviderInterface;

final class LaravelDecisionFactory implements DecisionFactoryInterface
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    private $app;

    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\DecisionFactoryInterface
     */
    private $decorated;

    /**
     * @var \StepTheFkUp\EasyDecision\Bridge\Laravel\ExpressionLanguageConfigFactoryInterface
     */
    private $expressionLanguageConfigFactory;

    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface[]
     */
    private $resolved = [];

    /**
     * LaravelDecisionFactory constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     * @param \StepTheFkUp\EasyDecision\Interfaces\DecisionFactoryInterface $decorated
     */
    public function __construct(Container $app, BaseDecisionFactoryInterface $decorated)
    {
        $this->app = $app;
        $this->decorated = $decorated;
    }

    /**
     * Create decision for given decision name.
     *
     * @param string $decision
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function create(string $decision): DecisionInterface
    {
        if (isset($this->resolved[$decision])) {
            return $this->resolved[$decision];
        }

        $config = \config(\sprintf('easy-decision.decisions.%s', $decision), null);

        if ($config === null) {
            throw new InvalidArgumentException(\sprintf('No decision configured for "%s"', $decision));
        }

        if (\is_array($config)) {
            return $this->resolved[$decision] = $this->doCreateForConfig($decision, $config);
        }

        if (\is_string($config) || $config instanceof DecisionConfigProviderInterface) {
            return $this->resolved[$decision] = $this->doCreateForConfigProvider($decision, $config);
        }

        throw new InvalidArgumentException(\sprintf(
            'Config for decision "%s" must be either an array, a string or an instance of %s, "%s" given',
            $decision,
            DecisionConfigProviderInterface::class,
            \gettype($config)
        ));
    }

    /**
     * Do create decision.
     *
     * @param string $decision
     * @param string $type
     * @param mixed[] $providers
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function doCreate(string $decision, string $type, array $providers): DecisionInterface
    {
        return $this->decorated->create(new DecisionConfig(
            $type,
            $this->getRuleProviders($providers),
            $this->getExpressionLanguageConfigFactory()->create($decision)
        ));
    }

    /**
     * Do create decision for given config.
     *
     * @param string $decision
     * @param mixed[] $config
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function doCreateForConfig(string $decision, array $config): DecisionInterface
    {
        if (empty($config['providers'] ?? null)) {
            throw new InvalidArgumentException(\sprintf('No rule providers configured for "%s"', $decision));
        }
        if (empty($config['type'] ?? null)) {
            throw new InvalidArgumentException(\sprintf('No decision type configured for "%s"', $decision));
        }

        return $this->doCreate($decision, (string)$config['type'], (array)$config['providers']);
    }

    /**
     * Do create decision for given config provider.
     *
     * @param string $decision
     * @param mixed $configProvider
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function doCreateForConfigProvider(string $decision, $configProvider): DecisionInterface
    {
        if (\is_string($configProvider)) {
            $configProvider = $this->app->make($configProvider);
        }

        if ($configProvider instanceof DecisionConfigProviderInterface) {
            return $this->doCreate($decision, $configProvider->getDecisionType(), $configProvider->getRuleProviders());
        }

        throw new InvalidArgumentException(\sprintf(
            'Decision config provider for "%s" must be an instance of "%s", "%s" given',
            $decision,
            DecisionConfigProviderInterface::class,
            \gettype($configProvider)
        ));
    }

    /**
     * Get expression language config factory.
     *
     * @return \StepTheFkUp\EasyDecision\Bridge\Laravel\ExpressionLanguageConfigFactoryInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getExpressionLanguageConfigFactory(): ExpressionLanguageConfigFactoryInterface
    {
        if ($this->expressionLanguageConfigFactory !== null) {
            return $this->expressionLanguageConfigFactory;
        }

        return $this->expressionLanguageConfigFactory = $this->app->make(
            ExpressionLanguageConfigFactoryInterface::class
        );
    }

    /**
     * Get rule providers.
     *
     * @param mixed[] $providers
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\RuleProviderInterface[]
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getRuleProviders(array $providers): array
    {
        $ruleProviders = [];

        foreach ($providers as $provider) {
            if ($provider instanceof RuleProviderInterface) {
                $ruleProviders[] = $provider;

                continue;
            }

            $ruleProviders[] = $this->app->make($provider);
        }

        return $ruleProviders;
    }
}

\class_alias(
    LaravelDecisionFactory::class,
    'LoyaltyCorp\EasyDecision\Bridge\Laravel\LaravelDecisionFactory',
    false
);
