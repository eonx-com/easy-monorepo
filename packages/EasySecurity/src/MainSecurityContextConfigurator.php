<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\ContextModifierInterface;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface as ConfiguratorInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasyUtils\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;

final class MainSecurityContextConfigurator
{
    /**
     * @var null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    private $apiToken;

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
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct(
        AuthorizationMatrixInterface $authorizationMatrix,
        Request $request,
        ?ApiTokenInterface $apiToken = null
    ) {
        $this->apiToken = $apiToken;
        $this->authorizationMatrix = $authorizationMatrix;
        $this->request = $request;
    }

    public function configure(SecurityContextInterface $securityContext): SecurityContextInterface
    {
        $securityContext->setAuthorizationMatrix($this->authorizationMatrix);
        $securityContext->setToken($this->apiToken);

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
                $modifier->modify($securityContext, $this->request);
            }
        }

        foreach ($this->configurators as $configurator) {
            $configurator->configure($securityContext, $this->request);
        }

        return $securityContext;
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface[]
     */
    public function getContextConfigurators(): array
    {
        return $this->configurators;
    }

    /**
     * @param iterable<mixed> $configurators
     */
    public function withConfigurators(iterable $configurators): self
    {
        $this->configurators = CollectorHelper::orderLowerPriorityFirst(
            CollectorHelper::filterByClass($configurators, ConfiguratorInterface::class)
        );

        return $this;
    }

    /**
     * @param iterable<mixed> $modifiers
     */
    public function withModifiers(iterable $modifiers): self
    {
        $this->modifiers = CollectorHelper::orderLowerPriorityFirst(
            CollectorHelper::filterByClass($modifiers, ContextModifierInterface::class)
        );

        return $this;
    }
}
