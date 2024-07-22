<?php
declare(strict_types=1);

namespace EonX\EasySecurity\SymfonySecurity\Entity;

use EonX\EasySecurity\Common\Entity\UserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * The current Symfony implementation requires to always have a User.
 * User on the security context is not required, use this class as a placeholder to trick Symfony ;).
 */
final class FakeUser implements UserInterface, SymfonyUserInterface
{
    public const ID_USERNAME = 'easy_security.fake_user';

    public function eraseCredentials(): void
    {
        // Do nothing
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return [];
    }

    public function getUserIdentifier(): string
    {
        return self::ID_USERNAME;
    }
}
