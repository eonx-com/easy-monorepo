<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Tokens;

use StepTheFkUp\ApiToken\Tests\AbstractTestCase;
use StepTheFkUp\ApiToken\Tokens\BasicAuthApiToken;

final class BasicAuthApiTokenTest extends AbstractTestCase
{
    /**
     * BasicAuthToken should return same values from getters and payload for password and username.
     *
     * @return void
     */
    public function testGettersShouldReturnSameValueAsPayload(): void
    {
        $token = $this->createBasicAuthApiToken();

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
        self::assertEquals('password', $this->createBasicAuthApiToken()->getPassword());
    }

    /**
     * BasicAuthToken should return the same username as given in input payload.
     *
     * @return void
     */
    public function testGetUsernameSuccessfully(): void
    {
        self::assertEquals('username', $this->createBasicAuthApiToken()->getUsername());
    }

    /**
     * Create BasicAuthApiToken.
     *
     * @return \StepTheFkUp\ApiToken\Tokens\BasicAuthApiToken
     */
    private function createBasicAuthApiToken(): BasicAuthApiToken
    {
        return new BasicAuthApiToken('username', 'password');
    }
}
