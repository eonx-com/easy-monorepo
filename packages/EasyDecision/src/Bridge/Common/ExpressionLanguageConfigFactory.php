<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Common;

use EonX\EasyDecision\Bridge\Common\Interfaces\DecisionConfigProviderInterface;
use EonX\EasyDecision\Bridge\Common\Interfaces\ExpressionLanguageConfigFactoryInterface;
use EonX\EasyDecision\Expressions\ExpressionLanguageConfig;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageConfigInterface;
use Psr\Container\ContainerInterface;

final class ExpressionLanguageConfigFactory implements ExpressionLanguageConfigFactoryInterface
{
    /**
     * @var mixed[]
     */
    private $config;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface
     */
    private $expressionFunctionFactory;

    /**
     * @var \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    private $globalExpressionFunctions;

    /**
     * @param mixed[] $config
     */
    public function __construct(array $config, ContainerInterface $container)
    {
        $this->config = $config;
        $this->container = $container;
    }

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
     * @return mixed[]
     */
    private function getDecisionExpressions(string $decision): array
    {
        $config = $this->config['decisions'][$decision] ?? [];
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

    private function getExpressionFunctionFactory(): ExpressionFunctionFactoryInterface
    {
        if ($this->expressionFunctionFactory !== null) {
            return $this->expressionFunctionFactory;
        }

        return $this->expressionFunctionFactory = $this->container->get(ExpressionFunctionFactoryInterface::class);
    }

    /**
     * @param mixed $provider
     */
    private function getExpressionFunctionProvider($provider): ExpressionFunctionProviderInterface
    {
        if ($provider instanceof ExpressionFunctionProviderInterface) {
            return $provider;
        }

        return $this->container->get($provider);
    }

    /**
     * @param mixed[] $functions
     * @param mixed[] $providers
     *
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
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
     * @return \EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionInterface[]
     */
    private function getGlobalExpressionFunctions(): array
    {
        if ($this->globalExpressionFunctions !== null) {
            return $this->globalExpressionFunctions;
        }

        $config = $this->config;

        return $this->globalExpressionFunctions = $this->getExpressionFunctions(
            $config['expressions']['functions'] ?? [],
            $config['expressions']['providers'] ?? []
        );
    }
}
