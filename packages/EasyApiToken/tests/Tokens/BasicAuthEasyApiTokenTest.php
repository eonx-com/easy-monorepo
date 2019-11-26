<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Tokens;

use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tokens\BasicAuthEasyApiToken;

final class BasicAuthEasyApiTokenTest extends AbstractTestCase
{
    /**
     * BasicAuthToken should return same values from getters and payload for password and username.
     *
     * @return void
     */
    public function testGettersShouldReturnSameValueAsPayload(): void
    {
        $token = $this->createBasicAuthEasyApiToken();

        self::assertEquals($token->getPassword(), $token->getPayload()['password']);
        self::assertEquals($token->getUsername(), $token->getPayload()['username']);
    }

    /**
     * BasicAuthToken should return the same password as given in input payload.
     *
     * @return void
     */
    public function testGetPasswordSuccessfully(): void
    {
        self::assertEquals('password', $this->createBasicAuthEasyApiToken()->getPassword());
    }

    /**
     * BasicAuthToken should return the same username as given in input payload.
     *
     * @return void
     */
    public function testGetUsernameSuccessfully(): void
    {
        self::assertEquals('username', $this->createBasicAuthEasyApiToken()->getUsername());
    }

    /**
     * Create BasicAuthEasyApiToken.
     *
     * @return \EonX\EasyApiToken\Tokens\BasicAuthEasyApiToken
     */
    private function createBasicAuthEasyApiToken(): BasicAuthEasyApiToken
    {
        return new BasicAuthEasyApiToken('username', 'password');
    }
}
