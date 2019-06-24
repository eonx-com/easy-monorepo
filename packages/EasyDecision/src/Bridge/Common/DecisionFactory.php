<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Bridge\Common;

use LoyaltyCorp\EasyDecision\Bridge\Common\Interfaces\DecisionConfigProviderInterface;
use LoyaltyCorp\EasyDecision\Bridge\Common\Interfaces\DecisionFactoryInterface;
use LoyaltyCorp\EasyDecision\Bridge\Common\Interfaces\ExpressionLanguageConfigFactoryInterface;
use LoyaltyCorp\EasyDecision\Decisions\DecisionConfig;
use LoyaltyCorp\EasyDecision\Exceptions\InvalidArgumentException;
use LoyaltyCorp\EasyDecision\Interfaces\DecisionFactoryInterface as BaseDecisionFactoryInterface;
use LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface;
use LoyaltyCorp\EasyDecision\Interfaces\RuleProviderInterface;
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
     * @var \LoyaltyCorp\EasyDecision\Interfaces\DecisionFactoryInterface
     */
    private $decorated;

    /**
     * @var \LoyaltyCorp\EasyDecision\Bridge\Common\Interfaces\ExpressionLanguageConfigFactoryInterface
     */
    private $expressionLanguageConfigFactory;

    /**
     * @var \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface[]
     */
    private $resolved = [];

    /**
     * DecisionFactory constructor.
     *
     * @param mixed[] $config
     * @param \Psr\Container\ContainerInterface $container
     * @param \LoyaltyCorp\EasyDecision\Interfaces\DecisionFactoryInterface $decorated
     */
    public function __construct(array $config, ContainerInterface $container, BaseDecisionFactoryInterface $decorated)
    {
        $this->config = $config;
        $this->container = $container;
        $this->decorated = $decorated;
    }

    /**
     * Create decision for given decision name.
     *
     * @param string $decision
     * @param mixed[]|null $params
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(string $decision, ?array $params = null): DecisionInterface
    {
        if (isset($this->resolved[$decision])) {
            return $this->resolved[$decision];
        }

        $config = $this->config['decisions'][$decision] ?? null;

        if ($config === null) {
            throw new InvalidArgumentException(\sprintf('No decision configured for "%s"', $decision));
        }

        if (\is_array($config)) {
            return $this->resolved[$decision] = $this->doCreateForConfig($decision, $config, $params);
        }

        if (\is_string($config) || $config instanceof DecisionConfigProviderInterface) {
            return $this->resolved[$decision] = $this->doCreateForConfigProvider($decision, $config, $params);
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
     * @param mixed[]|null $params
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    private function doCreate(
        string $decision,
        string $type,
        array $providers,
        ?array $params = null
    ): DecisionInterface {
        return $this->decorated->create(
            new DecisionConfig(
                $type,
                $decision,
                $this->getRuleProviders($providers),
                $this->getExpressionLanguageConfigFactory()->create($decision),
                $params
            )
        );
    }

    /**
     * Do create decision for given config.
     *
     * @param string $decision
     * @param mixed[] $config
     * @param mixed[]|null $params
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
     */
    private function doCreateForConfig(string $decision, array $config, ?array $params = null): DecisionInterface
    {
        if (empty($config['providers'] ?? null)) {
            throw new InvalidArgumentException(\sprintf('No rule providers configured for "%s"', $decision));
        }
        if (empty($config['type'] ?? null)) {
            throw new InvalidArgumentException(\sprintf('No decision type configured for "%s"', $decision));
        }

        return $this->doCreate($decision, (string)$config['type'], (array)$config['providers'], $params);
    }

    /**
     * Do create decision for given config provider.
     *
     * @param string $decision
     * @param mixed $configProvider
     * @param mixed[]|null $params
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\DecisionInterface
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
                $params
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
     * Get expression language config factory.
     *
     * @return \LoyaltyCorp\EasyDecision\Bridge\Common\Interfaces\ExpressionLanguageConfigFactoryInterface
     */
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
     * Get rule providers.
     *
     * @param mixed[] $providers
     *
     * @return \LoyaltyCorp\EasyDecision\Interfaces\RuleProviderInterface[]
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
