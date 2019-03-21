<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Bridge\Laravel;

use Illuminate\Contracts\Container\Container;
use StepTheFkUp\EasyDecision\Decisions\DecisionConfig;
use StepTheFkUp\EasyDecision\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyDecision\Expressions\ExpressionLanguageConfig;
use StepTheFkUp\EasyDecision\Interfaces\DecisionFactoryInterface as BaseDecisionFactoryInterface;
use StepTheFkUp\EasyDecision\Interfaces\DecisionInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;
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
     * @var \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface
     */
    private $expressionFunctionFactory;

    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    private $globalExpressionFunctions;

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
     * @param string $type
     * @param mixed[] $providers
     * @param mixed[] $expressions
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function doCreate(string $type, array $providers, array $expressions): DecisionInterface
    {
        return $this->decorated->create(new DecisionConfig(
            $type,
            $this->getRuleProviders($providers),
            $this->getExpressionLanguageConfig($expressions)
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

        return $this->doCreate(
            (string)$config['type'],
            (array)$config['providers'],
            (array)($config['expressions'] ?? [])
        );
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
            return $this->doCreate(
                $configProvider->getDecisionType(),
                $configProvider->getRuleProviders(),
                [
                    'functions' => $configProvider->getExpressionFunctions() ?? [],
                    'providers' => $configProvider->getExpressionFunctionProviders() ?? []
                ]
            );
        }

        throw new InvalidArgumentException(\sprintf(
            'Decision config provider for "%s" must be an instance of "%s", "%s" given',
            $decision,
            DecisionConfigProviderInterface::class,
            \gettype($configProvider)
        ));
    }

    /**
     * Get expression function factory.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getExpressionFunctionFactory(): ExpressionFunctionFactoryInterface
    {
        if ($this->expressionFunctionFactory !== null) {
            return $this->expressionFunctionFactory;
        }

        return $this->expressionFunctionFactory = $this->app->make(ExpressionFunctionFactoryInterface::class);
    }

    /**
     * Get expression function provider.
     *
     * @param mixed $provider
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getExpressionFunctionProvider($provider): ExpressionFunctionProviderInterface
    {
        if ($provider instanceof ExpressionFunctionProviderInterface) {
            return $provider;
        }

        return $this->app->make($provider);
    }

    /**
     * Get expression functions for given functions and providers.
     *
     * @param mixed[] $functions
     * @param mixed[] $providers
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getExpressionFunctions(array $functions, array $providers): array
    {
        $functionFactory = $this->getExpressionFunctionFactory();
        $expressionFunctions = [];

        foreach ($functions as $function) {
            $expressionFunctions[] = $functionFactory->create($function);
        }

        foreach ($providers as $provider) {
            foreach ($this->getExpressionFunctionProvider($provider)->getFunctions() as $function) {
                $expressionFunctions[] = $functionFactory->create($function);
            }
        }

        return $expressionFunctions;
    }

    /**
     * Get expression language config for given expression config.
     *
     * @param mixed[] $config
     *
     * @return null|\StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getExpressionLanguageConfig(array $config): ?ExpressionLanguageConfigInterface
    {
        $functions = $config['functions'] ?? [];
        $providers = $config['providers'] ?? [];
        $globals = $this->getGlobalExpressionFunctions();

        $expressionFunctions = $this->getExpressionFunctions($functions, $providers) + $globals;

        if (empty($expressionFunctions) === false) {
            return new ExpressionLanguageConfig(null, null, $expressionFunctions);
        }

        return null;
    }

    /**
     * Get global expression functions.
     *
     * @return \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getGlobalExpressionFunctions(): array
    {
        if ($this->globalExpressionFunctions !== null) {
            return $this->globalExpressionFunctions;
        }

        return $this->globalExpressionFunctions = $this->getExpressionFunctions(
            \config('easy-decision.expressions.functions', []),
            \config('easy-decision.expressions.providers', [])
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
