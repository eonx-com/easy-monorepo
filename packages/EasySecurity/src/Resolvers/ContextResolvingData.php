<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Resolvers;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;
use EonX\EasySecurity\Interfaces\UserInterface;
use Symfony\Component\HttpFoundation\Request;

final class ContextResolvingData implements ContextResolvingDataInterface
{
    /**
     * @var null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    private $apiToken;

    /**
     * @var null|\EonX\EasySecurity\Interfaces\ProviderInterface
     */
    private $provider;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var null|\EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    private $roles;

    /**
     * @var null|\EonX\EasySecurity\Interfaces\UserInterface
     */
    private $user;

    /**
     * ContextResolvingData constructor.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface $apiToken
     */
    public function __construct(Request $request, ?EasyApiTokenInterface $apiToken = null)
    {
        $this->request = $request;
        $this->apiToken = $apiToken;
    }

    /**
     * Get api token.
     *
     * @return null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    public function getApiToken(): ?EasyApiTokenInterface
    {
        return $this->apiToken;
    }

    /**
     * Get provider.
     *
     * @return null|\EonX\EasySecurity\Interfaces\ProviderInterface
     */
    public function getProvider(): ?ProviderInterface
    {
        return $this->provider;
    }

    /**
     * Get request.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get roles.
     *
     * @return null|\EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    /**
     * Get user.
     *
     * @return null|\EonX\EasySecurity\Interfaces\UserInterface
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * Set provider.
     *
     * @param null|\EonX\EasySecurity\Interfaces\ProviderInterface $provider
     *
     * @return \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface
     */
    public function setProvider(?ProviderInterface $provider = null): ContextResolvingDataInterface
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Set roles.
     *
     * @param null|\EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     *
     * @return \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface
     */
    public function setRoles(?array $roles = null): ContextResolvingDataInterface
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Set user.
     *
     * @param null|\EonX\EasySecurity\Interfaces\UserInterface $user
     *
     * @return \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface
     */
    public function setUser(?UserInterface $user = null): ContextResolvingDataInterface
    {
        $this->user = $user;

        return $this;
    }
}
