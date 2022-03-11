<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security;

use EonX\EasySecurity\Interfaces\UserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * The current implement of Symfony bridge using Guard requires to always have a User.
 * User on the security context is not required, use this class as a placeholder to trick Symfony ;).
 */
final class FakeUser implements UserInterface, SymfonyUserInterface
{
    /**
     * @var string
     */
    public const ID_USERNAME = 'easy_security.fake_user';

    public function eraseCredentials(): void
    {
        // Do nothing.
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

    public function getUniqueId(): string
    {
        return self::ID_USERNAME;
    }

    public function getUserIdentifier(): string
    {
        return self::ID_USERNAME;
    }

    public function getUsername(): string
    {
        return self::ID_USERNAME;
    }
}
