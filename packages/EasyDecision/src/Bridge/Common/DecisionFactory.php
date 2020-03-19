<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Common;

use EonX\EasyDecision\Bridge\Common\Interfaces\DecisionConfigProviderInterface;
use EonX\EasyDecision\Bridge\Common\Interfaces\DecisionFactoryInterface;
use EonX\EasyDecision\Bridge\Common\Interfaces\ExpressionLanguageConfigFactoryInterface;
use EonX\EasyDecision\Decisions\DecisionConfig;
use EonX\EasyDecision\Exceptions\InvalidArgumentException;
use EonX\EasyDecision\Interfaces\DecisionFactoryInterface as BaseDecisionFactoryInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use EonX\EasyDecision\Interfaces\RuleProviderInterface;
use Psr\Container\ContainerInterface;

final class DecisionFactory implements DecisionFactoryInterface
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
     * @var \EonX\EasyDecision\Interfaces\DecisionFactoryInterface
     */
    private $decorated;

    /**
     * @var \EonX\EasyDecision\Bridge\Common\Interfaces\ExpressionLanguageConfigFactoryInterface
     */
    private $expressionLanguageConfigFactory;

    /**
     * @param mixed[] $config
     */
    public function __construct(array $config, ContainerInterface $container, BaseDecisionFactoryInterface $decorated)
    {
        $this->config = $config;
        $this->container = $container;
        $this->decorated = $decorated;
    }

    /**
     * @param mixed[]|null $params
     */
    public function create(string $decision, ?array $params = null): DecisionInterface
    {
        $config = $this->config['decisions'][$decision] ?? null;

        if ($config === null) {
            throw new InvalidArgumentException(\sprintf('No decision configured for "%s"', $decision));
        }

        if (\is_array($config)) {
            return $this->doCreateForConfig($decision, $config, $params);
        }

        if (\is_string($config) || $config instanceof DecisionConfigProviderInterface) {
            return $this->doCreateForConfigProvider($decision, $config, $params);
        }

        throw new InvalidArgumentException(\sprintf(
            'Config for decision "%s" must be either an array, a string or an instance of %s, "%s" given',
            $decision,
            DecisionConfigProviderInterface::class,
            \gettype($config)
        ));
    }

    /**
     * @param mixed[] $providers
     * @param mixed[]|null $params
     * @param null|mixed $defaultOutput
     */
    private function doCreate(
        string $decision,
        string $type,
        array $providers,
        ?array $params = null,
        $defaultOutput = null
    ): DecisionInterface {
        return $this->decorated->create(
            new DecisionConfig(
                $type,
                $decision,
                $this->getRuleProviders($providers),
                $this->getExpressionLanguageConfigFactory()->create($decision),
                $params,
                $defaultOutput
            )
        );
    }

    /**
     * @param mixed[] $config
     * @param mixed[]|null $params
     */
    private function doCreateForConfig(string $decision, array $config, ?array $params = null): DecisionInterface
    {
        if (empty($config['providers'] ?? null)) {
            throw new InvalidArgumentException(\sprintf('No rule providers configured for "%s"', $decision));
        }
        if (empty($config['type'] ?? null)) {
            throw new InvalidArgumentException(\sprintf('No decision type configured for "%s"', $decision));
        }

        return $this->doCreate(
            $decision,
            (string)$config['type'],
            (array)$config['providers'],
            $params,
            $config['default_output'] ?? null
        );
    }

    /**
     * @param mixed $configProvider
     * @param null|mixed[] $params
     */
    private function doCreateForConfigProvider(
        string $decision,
        $configProvider,
        ?array $params = null
    ): DecisionInterface {
        if (\is_string($configProvider)) {
            $configProvider = $this->container->get($configProvider);
        }

        if ($configProvider instanceof DecisionConfigProviderInterface) {
            return $this->doCreate(
                $decision,
                $configProvider->getDecisionType(),
                $configProvider->getRuleProviders(),
                $params,
                $configProvider->getDefaultOutput()
            );
        }

        throw new InvalidArgumentException(\sprintf(
            'Decision config provider for "%s" must be an instance of "%s", "%s" given',
            $decision,
            DecisionConfigProviderInterface::class,
            \gettype($configProvider)
        ));
    }

    private function getExpressionLanguageConfigFactory(): ExpressionLanguageConfigFactoryInterface
    {
        if ($this->expressionLanguageConfigFactory !== null) {
            return $this->expressionLanguageConfigFactory;
        }

        return $this->expressionLanguageConfigFactory = $this->container->get(
            ExpressionLanguageConfigFactoryInterface::class
        );
    }

    /**
     * @param mixed[] $providers
     *
     * @return \EonX\EasyDecision\Interfaces\RuleProviderInterface[]
     */
    private function getRuleProviders(array $providers): array
    {
        return \array_map(function ($provider): RuleProviderInterface {
            if ($provider instanceof RuleProviderInterface) {
                return $provider;
            }

            return $this->container->get($provider);
        }, $providers);
    }
}
