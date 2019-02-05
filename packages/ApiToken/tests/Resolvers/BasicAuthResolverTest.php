<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Resolvers;

use StepTheFkUp\ApiToken\Interfaces\Tokens\BasicAuthApiTokenInterface;
use StepTheFkUp\ApiToken\Resolvers\BasicAuthResolver;
use StepTheFkUp\ApiToken\Tests\AbstractTestCase;
use StepTheFkUp\ApiToken\Tokens\BasicAuthApiToken;

final class BasicAuthResolverTest extends AbstractTestCase
{
    /**
     * BasicAuthResolver should return null if Authorization header not set.
     *
     * @return void
     */
    public function testBasicAuthNullIfAuthorizationHeaderNotSet(): void
    {
        self::assertNull((new BasicAuthResolver())->resolve($this->createServerRequest()));
    }

    /**
     * BasicAuthResolver should return null if Authorization header doesn't start with "Basic ".
     *
     * @return void
     */
    public function testBasicAuthNullIfDoesntStartWithBasic(): void
    {
        self::assertNull((new BasicAuthResolver())->resolve($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'SomethingElse'
        ])));
    }

    /**
     * BasicAuthResolver should return null if Authorization header doesn't contain any username.
     *
     * @return void
     */
    public function testBasicAuthNullIfNoUsernameOrPasswordProvided(): void
    {
        $tests = [
            '',
            ':',
            'username',
            'username:',
            ':password'
        ];

        foreach ($tests as $test) {
            self::assertNull((new BasicAuthResolver())->resolve($this->createServerRequest([
                'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode($test)
            ])));
        }
    }

    /**
     * BasicAuthResolver should return BasicAuthToken and expected username and password.
     *
     * @return void
     */
    public function testBasicAuthReturnApiTokenSuccessfully(): void
    {
        // Value in header => [expectedUsername, expectedPassword]
        $tests = [
            'username:password' => ['username', 'password'],
            'username : password ' => ['username', 'password'],
            'username:Sp3c|@l_cH\\aracters' => ['username', 'Sp3c|@l_cH\\aracters']
        ];

        foreach ($tests as $test => $expected) {
            $token = (new BasicAuthResolver())->resolve($this->createServerRequest([
                'HTTP_AUTHORIZATION' => \sprintf('Basic %s', \base64_encode($test))
            ]));

            self::assertInstanceOf(BasicAuthApiTokenInterface::class, $token);
            self::assertEquals(BasicAuthApiToken::class, $token->getStrategy());
            self::assertEquals($expected[0], $token->getPayload()['username']);
            self::assertEquals($expected[1], $token->getPayload()['password']);
        }
    }
}