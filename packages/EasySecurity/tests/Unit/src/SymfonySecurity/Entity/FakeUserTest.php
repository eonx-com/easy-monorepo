<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\SymfonySecurity\Entity;

use EonX\EasySecurity\SymfonySecurity\Entity\FakeUser;
use EonX\EasySecurity\Tests\Unit\AbstractSymfonyTestCase;

final class FakeUserTest extends AbstractSymfonyTestCase
{
    public function testGetters(): void
    {
        $user = new FakeUser();
        // For coverage
        $user->eraseCredentials();

        self::assertEmpty($user->getRoles());
        self::assertSame('easy_security.fake_user', $user->getUserIdentifier());
    }
}
