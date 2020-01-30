<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Resolvers;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\UserInterface;
use Symfony\Component\HttpFoundation\Request;

interface ContextResolvingDataInterface
{
    public function getApiToken(): ?EasyApiTokenInterface;

    public function getProvider(): ?ProviderInterface;

    public function getRequest(): Request;

    public function getRoles(): array;

    public function getUser(): ?UserInterface;

    public function setProvider(?ProviderInterface $provider = null): self;

    public function setRoles(?array $roles = null): self;

    public function setUser(?UserInterface $user = null): self;
}
