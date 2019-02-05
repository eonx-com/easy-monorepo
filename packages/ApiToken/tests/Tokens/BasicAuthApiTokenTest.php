<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Tokens;

use StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException;
use StepTheFkUp\ApiToken\Tests\AbstractTestCase;
use StepTheFkUp\ApiToken\Tokens\BasicAuthApiToken;

final class BasicAuthApiTokenTest extends AbstractTestCase
{
    /**
     * BasicAuthToken should return same values from getters and payload for password and username.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException
     */
    public function testGettersShouldReturnSameValueAsPayload(): void
    {
        $token = new BasicAuthApiToken(['username' => 'username', 'password' => 'password']);

        self::assertEquals($token->getPassword(), $token->getPayload()['password']);
        self::assertEquals($token->getUsername(), $token->getPayload()['username']);
    }

    /**
     * BasicAuthToken should return the same password as given in input payload.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException
     */
    public function testGetPasswordSuccessfully(): void
    {
        $password = 'my-password';

        self::assertEquals($password, (new BasicAuthApiToken(\compact('password')))->getPassword());
    }

    /**
     * BasicAuthToken should return the same username as given in input payload.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException
     */
    public function testGetUsernameSuccessfully(): void
    {
        $username = 'my-username';

        self::assertEquals($username, (new BasicAuthApiToken(\compact('username')))->getUsername());
    }

    /**
     * BasicAuthToken should throw an exception if password not set in payload.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException
     */
    public function testGetPasswordThrowsExceptionIfEmpty(): void
    {
        $this->expectException(EmptyRequiredPayloadException::class);

        (new BasicAuthApiToken([]))->getPassword();
    }

    /**
     * BasicAuthToken should throw an exception if username not set in payload.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\EmptyRequiredPayloadException
     */
    public function testGetUsernameThrowsExceptionIfEmpty(): void
    {
        $this->expectException(EmptyRequiredPayloadException::class);

        (new BasicAuthApiToken([]))->getUsername();
    }

    /**
     * BasicAuthToken should return expected strategy.
     *
     * @return void
     */
    public function testGetStrategy(): void
    {
        self::assertEquals(BasicAuthApiToken::class, (new BasicAuthApiToken([]))->getStrategy());
    }
}