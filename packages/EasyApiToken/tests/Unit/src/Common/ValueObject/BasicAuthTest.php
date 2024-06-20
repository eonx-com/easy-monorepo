<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\ValueObject;

use EonX\EasyApiToken\Common\ValueObject\BasicAuth;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;

final class BasicAuthTest extends AbstractUnitTestCase
{
    public function testGetPasswordSuccessfully(): void
    {
        self::assertSame('password', $this->createBasicAuth()->getPassword());
    }

    public function testGetUsernameSuccessfully(): void
    {
        self::assertSame('username', $this->createBasicAuth()->getUsername());
    }

    public function testGettersShouldReturnSameValueAsPayload(): void
    {
        $token = $this->createBasicAuth();

        self::assertSame($token->getPassword(), $token->getPayload()['password']);
        self::assertSame($token->getUsername(), $token->getPayload()['username']);
        self::assertSame('original', $token->getOriginalToken());
    }

    private function createBasicAuth(): BasicAuth
    {
        return new BasicAuth('username', 'password', 'original');
    }
}
