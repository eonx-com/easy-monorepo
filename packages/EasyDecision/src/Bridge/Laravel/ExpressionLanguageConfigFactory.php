<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyDecision\Bridge\Laravel;

use Illuminate\Contracts\Container\Container;
use StepTheFkUp\EasyDecision\Expressions\ExpressionLanguageConfig;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface;
use StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;

final class ExpressionLanguageConfigFactory implements ExpressionLanguageConfigFactoryInterface
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    private $app;

    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface
     */
    private $expressionFunctionFactory;

    /**
     * @var \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    private $globalExpressionFunctions;

    /**
     * ExpressionLanguageConfigFactory constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Create expression language config for given decision.
     *
     * @param string $decision
     *
     * @return null|\StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function create(string $decision): ?ExpressionLanguageConfigInterface
    {
        $expressions = $this->getDecisionExpressions($decision);

        $globals = $this->getGlobalExpressionFunctions();

        $expressionFunctions = $this->getExpressionFunctions($expressions['functions'], $expressions['providers']);

        if (empty($expressionFunctions) === false) {
            return new ExpressionLanguageConfig(null, null, $expressionFunctions + $globals);
        }

        return null;
    }

    /**
     * Get decision expressions config.
     *
     * @param string $decision
     *
     * @return mixed[]
     */
    private function getDecisionExpressions(string $decision): array
    {
        $config = \config(\sprintf('easy-decision.decisions.%s', $decision), []);
        $functions = [];
        $providers = [];

        if (\is_array($config)) {
            $functions = $config['expressions']['functions'] ?? [];
            $providers = $config['expressions']['providers'] ?? [];
        }

        if ($config instanceof DecisionConfigProviderInterface) {
            $functions = $config->getExpressionFunctions();
            $providers = $config->getExpressionFunctionProviders();
        }

        return \compact('functions', 'providers');
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
}

\class_alias(
    ExpressionLanguageConfigFactory::class,
    'LoyaltyCorp\EasyDecision\Bridge\Laravel\ExpressionLanguageConfigFactory',
    false
);
