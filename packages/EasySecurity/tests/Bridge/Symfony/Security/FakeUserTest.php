<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Security\FakeUser;
use EonX\EasySecurity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;

final class FakeUserTest extends AbstractSymfonyTestCase
{
    public function testGetters(): void
    {
        $user = new FakeUser();
        // For coverage
        $user->eraseCredentials();

        self::assertEmpty($user->getRoles());
        self::assertEquals(FakeUser::ID_USERNAME, $user->getUserIdentifier());
    }
}
