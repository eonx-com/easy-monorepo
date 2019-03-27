<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Decoders;

use StepTheFkUp\ApiToken\Decoders\BasicAuthDecoder;
use StepTheFkUp\ApiToken\Interfaces\Tokens\BasicAuthApiTokenInterface;
use StepTheFkUp\ApiToken\Tests\AbstractTestCase;

final class BasicAuthDecoderTest extends AbstractTestCase
{
    /**
     * BasicAuthDecoder should return null if Authorization header not set.
     *
     * @return void
     */
    public function testBasicAuthNullIfAuthorizationHeaderNotSet(): void
    {
        self::assertNull((new BasicAuthDecoder())->decode($this->createServerRequest()));
    }

    /**
     * BasicAuthDecoder should return null if Authorization header doesn't start with "Basic ".
     *
     * @return void
     */
    public function testBasicAuthNullIfDoesntStartWithBasic(): void
    {
        self::assertNull((new BasicAuthDecoder())->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'SomethingElse'
        ])));
    }

    /**
     * BasicAuthDecoder should return null if Authorization header doesn't contain any username or password.
     *
     * @return void
     */
    public function testBasicAuthNullIfNoUsernameOrPasswordProvided(): void
    {
        $tests = [
            '',
            ':',
            ' : ',
            'username',
            'username:',
            ':password'
        ];

        foreach ($tests as $test) {
            self::assertNull((new BasicAuthDecoder())->decode($this->createServerRequest([
                'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode($test)
            ])));
        }
    }

    /**
     * BasicAuthDecoder should return BasicAuthToken and expected username and password.
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
            /** @var \StepTheFkUp\ApiToken\Interfaces\Tokens\BasicAuthApiTokenInterface $token */
            $token = (new BasicAuthDecoder())->decode($this->createServerRequest([
                'HTTP_AUTHORIZATION' => \sprintf('Basic %s', \base64_encode($test))
            ]));

            self::assertInstanceOf(BasicAuthApiTokenInterface::class, $token);
            self::assertEquals($expected[0], $token->getPayload()['username']);
            self::assertEquals($expected[1], $token->getPayload()['password']);
        }
    }
}
