<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\ContextModifierInterface;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface as ConfiguratorInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Traversable;

final class SecurityContextResolver implements SecurityContextResolverInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextInterface
     */
    private $context;

    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface[]
     */
    private $contextConfigurators;

    /**
     * @var \EonX\EasySecurity\Interfaces\ContextModifierInterface[]
     */
    private $contextModifiers;

    /**
     * @var \EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface
     */
    private $psr7Factory;

    /**
     * @var \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     */
    private $tokenDecoder;

    /**
     * @param mixed[]|iterable<mixed> $contextModifiers
     * @param mixed[]|iterable<mixed> $contextConfigurators
     */
    public function __construct(
        AuthorizationMatrixInterface $authorizationMatrix,
        SecurityContextInterface $context,
        EasyPsr7FactoryInterface $psr7Factory,
        EasyApiTokenDecoderInterface $tokenDecoder,
        iterable $contextModifiers,
        iterable $contextConfigurators
    ) {
        $context->setAuthorizationMatrix($authorizationMatrix);

        $this->context = $context;
        $this->psr7Factory = $psr7Factory;
        $this->tokenDecoder = $tokenDecoder;

        $this->setContextModifiers($contextModifiers);
        $this->setContextConfigurators($contextConfigurators);
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface[]
     */
    public function getContextConfigurators(): array
    {
        return $this->contextConfigurators;
    }

    public function resolve(Request $request): SecurityContextInterface
    {
        $this->context->setToken($this->tokenDecoder->decode($this->psr7Factory->createRequest($request)));

        if (empty($this->contextModifiers) === false) {
            @\trigger_error(
                \sprintf(
                    'Using %s is deprecated since 2.4 and will be removed in 3.0. Use %s instead',
                    ContextModifierInterface::class,
                    ConfiguratorInterface::class
                ),
                \E_USER_DEPRECATED
            );

            foreach ($this->contextModifiers as $modifier) {
                $modifier->modify($this->context, $request);
            }
        }

        foreach ($this->contextConfigurators as $configurator) {
            $configurator->configure($this->context, $request);
        }

        return $this->context;
    }

    /**
     * @param mixed[]|iterable<mixed> $configurators
     */
    private function setContextConfigurators(iterable $configurators): void
    {
        $configurators = $configurators instanceof Traversable
            ? \iterator_to_array($configurators)
            : (array)$configurators;

        $filter = static function ($configurator): bool {
            return $configurator instanceof ConfiguratorInterface;
        };
        $sort = static function (ConfiguratorInterface $first, ConfiguratorInterface $second): int {
            return $first->getPriority() <=> $second->getPriority();
        };

        $configurators = \array_filter($configurators, $filter);
        \usort($configurators, $sort);

        $this->contextConfigurators = $configurators;
    }

    /**
     * @param mixed[]|iterable<mixed> $modifiers
     */
    private function setContextModifiers(iterable $modifiers): void
    {
        $modifiers = $modifiers instanceof Traversable ? \iterator_to_array($modifiers) : (array)$modifiers;

        $filter = static function ($resolver): bool {
            return $resolver instanceof ContextModifierInterface;
        };
        $sort = static function (ContextModifierInterface $first, ContextModifierInterface $second): int {
            return $first->getPriority() <=> $second->getPriority();
        };

        $modifiers = \array_filter($modifiers, $filter);
        \usort($modifiers, $sort);

        $this->contextModifiers = $modifiers;
    }
}
