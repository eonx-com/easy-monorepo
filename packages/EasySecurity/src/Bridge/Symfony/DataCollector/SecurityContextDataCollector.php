<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DataCollector;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasySecurity\Authorization\AuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\CachedAuthorizationMatrixFactory;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use EonX\EasySecurity\Interfaces\UserInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class SecurityContextDataCollector extends DataCollector
{
    /**
     * @var string
     */
    public const NAME = 'easy_security.security_context_collector';

    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface
     */
    private $authorizationMatrixFactory;

    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextResolverInterface
     */
    private $securityContextResolver;

    /**
     * @param iterable<mixed> $configurators
     */
    public function __construct(
        AuthorizationMatrixFactoryInterface $authorizationMatrixFactory,
        SecurityContextResolverInterface $securityContextResolver,
        iterable $configurators,
    ) {
        $this->authorizationMatrixFactory = $authorizationMatrixFactory;
        $this->securityContextResolver = $securityContextResolver;

        $this->configurators = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($configurators, SecurityContextConfiguratorInterface::class)
        );
    }

    public function collect(Request $request, Response $response, ?\Throwable $throwable = null): void
    {
        $securityContext = $this->securityContextResolver->resolveContext();

        $this->data['authorization_matrix'] = $securityContext->getAuthorizationMatrix();
        $this->data['permissions'] = $securityContext->getPermissions();
        $this->data['roles'] = $securityContext->getRoles();
        $this->data['provider'] = $securityContext->getProvider();
        $this->data['user'] = $securityContext->getUser();
        $this->data['token'] = $securityContext->getToken();

        $this->setContextConfigurators();
        $this->setRolesPermissionsProviders();
    }

    public function getAuthorizationMatrix(): AuthorizationMatrixInterface
    {
        return $this->data['authorization_matrix'];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    public function getPermissions(): array
    {
        return $this->data['permissions'] ?? [];
    }

    /**
     * @return mixed[]
     */
    public function getPermissionsProviders(): array
    {
        return $this->data['permissions_providers'];
    }

    public function getProvider(): ?ProviderInterface
    {
        return $this->data['provider'] ?? null;
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->data['roles'] ?? [];
    }

    /**
     * @return mixed[]
     */
    public function getRolesProviders(): array
    {
        return $this->data['roles_providers'];
    }

    /**
     * @return mixed[]
     */
    public function getSecurityContextConfigurators(): array
    {
        return $this->data['context_configurators'] ?? [];
    }

    public function getToken(): ?ApiTokenInterface
    {
        return $this->data['token'] ?? null;
    }

    public function getUser(): ?UserInterface
    {
        return $this->data['user'] ?? null;
    }

    public function reset(): void
    {
        $this->data = [];
    }

    private function setContextConfigurators(): void
    {
        $this->data['context_configurators'] = [];

        foreach ($this->configurators as $contextConfigurator) {
            $reflection = new \ReflectionClass($contextConfigurator);

            $this->data['context_configurators'][] = [
                'class' => $reflection->getName(),
                'filename' => $reflection->getFileName(),
                'priority' => $contextConfigurator->getPriority(),
            ];
        }
    }

    private function setRolesPermissionsProviders(): void
    {
        $this->data['roles_providers'] = [];
        $this->data['permissions_providers'] = [];

        $factory = $this->authorizationMatrixFactory instanceof CachedAuthorizationMatrixFactory
            ? $this->authorizationMatrixFactory->getDecorated()
            : $this->authorizationMatrixFactory;

        if ($factory instanceof AuthorizationMatrixFactory === false) {
            return;
        }

        foreach ($factory->getRolesProviders() as $rolesProvider) {
            $reflection = new \ReflectionClass($rolesProvider);

            $this->data['roles_providers'][] = [
                'class' => $reflection->getName(),
                'filename' => $reflection->getFileName(),
            ];
        }

        foreach ($factory->getPermissionsProviders() as $permissionsProvider) {
            $reflection = new \ReflectionClass($permissionsProvider);

            $this->data['permissions_providers'][] = [
                'class' => $reflection->getName(),
                'filename' => $reflection->getFileName(),
            ];
        }
    }
}
