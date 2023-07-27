<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Tokens;

use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tokens\BasicAuth;

final class BasicAuthEasyApiTokenTest extends AbstractTestCase
{
    public function testGetPasswordSuccessfully(): void
    {
        self::assertEquals('password', $this->createBasicAuthEasyApiToken()->getPassword());
    }

    public function testGetUsernameSuccessfully(): void
    {
        self::assertEquals('username', $this->createBasicAuthEasyApiToken()->getUsername());
    }

    public function testGettersShouldReturnSameValueAsPayload(): void
    {
        $token = $this->createBasicAuthEasyApiToken();

        self::assertEquals($token->getPassword(), $token->getPayload()['password']);
        self::assertEquals($token->getUsername(), $token->getPayload()['username']);
        self::assertEquals($token->getOriginalToken(), 'original');
    }

    private function createBasicAuthEasyApiToken(): BasicAuth
    {
        return new BasicAuth('username', 'password', 'original');
    }
}
