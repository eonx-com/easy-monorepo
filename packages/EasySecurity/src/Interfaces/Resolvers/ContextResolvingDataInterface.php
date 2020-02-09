<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Resolvers;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\UserInterface;
use Symfony\Component\HttpFoundation\Request;

interface ContextResolvingDataInterface
{
    /**
     * Get api token.
     *
     * @return null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    public function getApiToken(): ?EasyApiTokenInterface;

    /**
     * Get provider.
     *
     * @return null|\EonX\EasySecurity\Interfaces\ProviderInterface
     */
    public function getProvider(): ?ProviderInterface;

    /**
     * Get request.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest(): Request;

    /**
     * Get roles.
     *
     * @return null|\EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): ?array;

    /**
     * Get user.
     *
     * @return null|\EonX\EasySecurity\Interfaces\UserInterface
     */
    public function getUser(): ?UserInterface;

    /**
     * Set provider.
     *
     * @param null|\EonX\EasySecurity\Interfaces\ProviderInterface $provider
     *
     * @return \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface
     */
    public function setProvider(?ProviderInterface $provider = null): self;

    /**
     * Set roles.
     *
     * @param null|\EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     *
     * @return \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface
     */
    public function setRoles(?array $roles = null): self;

    /**
     * Set user.
     *
     * @param null|\EonX\EasySecurity\Interfaces\UserInterface $user
     *
     * @return \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface
     */
    public function setUser(?UserInterface $user = null): self;
}
