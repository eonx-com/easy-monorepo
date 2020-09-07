<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\EasyBugsnag;

use Bugsnag\Client;
use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasySecurity\Interfaces\Authorization\PermissionInterface;
use EonX\EasySecurity\Interfaces\Authorization\RoleInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\UserInterface;

final class SecurityContextClientConfigurator extends AbstractClientConfigurator
{
    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextInterface
     */
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext, ?int $priority = null)
    {
        $this->securityContext = $securityContext;

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $security = [];

        if ($this->securityContext->getToken()) {
            $security['token'] = $this->formatToken($this->securityContext->getToken());
        }

        if (\count($this->securityContext->getRoles()) > 0) {
            $security['roles'] = $this->formatRoles($this->securityContext->getRoles());
        }

        if (\count($this->securityContext->getPermissions()) > 0) {
            $security['permissions'] = $this->formatPermissions($this->securityContext->getPermissions());
        }

        if ($this->securityContext->getProvider()) {
            $security['provider'] = $this->formatProvider($this->securityContext->getProvider());
        }

        if ($this->securityContext->getUser()) {
            $security['user'] = $this->formatUser($this->securityContext->getUser());
        }

        $bugsnag->setMetaData(['security' => $security]);
    }

    /**
     * @param \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[] $permissions
     *
     * @return mixed[]
     */
    private function formatPermissions(array $permissions): array
    {
        $map = static function (PermissionInterface $permission): string {
            return (string)$permission;
        };

        return \array_values(\array_map($map, $permissions));
    }

    /**
     * @return mixed[]
     */
    private function formatProvider(ProviderInterface $provider): array
    {
        return ['class' => \get_class($provider), 'id' => $provider->getUniqueId()];
    }

    /**
     * @param \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[] $roles
     *
     * @return mixed[]
     */
    private function formatRoles(array $roles): array
    {
        $map = static function (RoleInterface $role): string {
            return (string)$role;
        };

        return \array_values(\array_map($map, $roles));
    }

    /**
     * @return mixed[]
     */
    private function formatToken(ApiTokenInterface $apiToken): array
    {
        return ['class' => \get_class($apiToken), 'original' => $apiToken->getOriginalToken()];
    }

    /**
     * @return mixed[]
     */
    private function formatUser(UserInterface $user): array
    {
        return ['class' => \get_class($user), 'id' => $user->getUniqueId()];
    }
}
