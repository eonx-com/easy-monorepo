<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Stubs;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUser;

final class SymfonyUserStub implements SymfonyUser
{
    /**
     * @var string
     */
    private $username;

    public function __construct(?string $username = null)
    {
        $this->username = $username ?? 'username';
    }

    public function eraseCredentials(): void
    {
    }

    public function getPassword(): ?string
    {
        return null;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return [];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
