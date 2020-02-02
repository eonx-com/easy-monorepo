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

    public function __construct(Request $request, ?EasyApiTokenInterface $apiToken = null)
    {
        $this->request = $request;
        $this->apiToken = $apiToken;
    }

    public function getApiToken(): ?EasyApiTokenInterface
    {
        return $this->apiToken;
    }

    public function getProvider(): ?ProviderInterface
    {
        return $this->provider;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setProvider(?ProviderInterface $provider = null): ContextResolvingDataInterface
    {
        $this->provider = $provider;

        return $this;
    }

    public function setRoles(?array $roles = null): ContextResolvingDataInterface
    {
        $this->roles = $roles;

        return $this;
    }

    public function setUser(?UserInterface $user = null): ContextResolvingDataInterface
    {
        $this->user = $user;

        return $this;
    }
}
