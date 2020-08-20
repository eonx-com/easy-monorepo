<?php
declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;
use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\ContextModifierInterface;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface as ConfiguratorInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Traversable;

final class MainSecurityContextConfigurator
{
    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface
     */
    private $authorizationMatrix;

    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface[]
     */
    private $configurators = [];

    /**
     * @var \EonX\EasySecurity\Interfaces\ContextModifierInterface[]
     */
    private $modifiers = [];

    /**
     * @var \EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface
     */
    private $psr7Factory;

    /**
     * @var null|string
     */
    private $tokenDecoder;

    /**
     * @var \EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface
     */
    private $tokenDecoderFactory;

    public function __construct(
        AuthorizationMatrixInterface $authorizationMatrix,
        EasyApiTokenDecoderFactoryInterface $tokenDecoderFactory,
        EasyPsr7FactoryInterface $psr7Factory
    ) {
        $this->authorizationMatrix = $authorizationMatrix;
        $this->tokenDecoderFactory = $tokenDecoderFactory;
        $this->psr7Factory = $psr7Factory;
    }

    public function configure(SecurityContextInterface $securityContext, Request $request): SecurityContextInterface
    {
        $securityContext->setAuthorizationMatrix($this->authorizationMatrix);
        $securityContext->setToken(
            $this->tokenDecoderFactory->build($this->tokenDecoder)->decode($this->psr7Factory->createRequest($request))
        );

        if (empty($this->modifiers) === false) {
            @\trigger_error(
                \sprintf(
                    'Using %s is deprecated since 2.4 and will be removed in 3.0. Use %s instead',
                    ContextModifierInterface::class,
                    ConfiguratorInterface::class
                ),
                \E_USER_DEPRECATED
            );

            foreach ($this->modifiers as $modifier) {
                $modifier->modify($securityContext, $request);
            }
        }

        foreach ($this->configurators as $configurator) {
            $configurator->configure($securityContext, $request);
        }

        return $securityContext;
    }

    /**
     * @param iterable<mixed> $configurators
     */
    public function withConfigurators(iterable $configurators): self
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

        $this->configurators = $configurators;

        return $this;
    }

    /**
     * @param iterable<mixed> $modifiers
     */
    public function withModifiers(iterable $modifiers): self
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

        $this->modifiers = $modifiers;

        return $this;
    }

    public function withTokenDecoder(?string $tokenDecoder = null): self
    {
        $this->tokenDecoder = $tokenDecoder;

        return $this;
    }
}
