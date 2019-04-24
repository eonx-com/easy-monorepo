<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Encoders;

use LoyaltyCorp\EasyApiToken\Encoders\BasicAuthEncoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException;
use LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use LoyaltyCorp\EasyApiToken\Tests\AbstractTestCase;
use LoyaltyCorp\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use LoyaltyCorp\EasyApiToken\Tokens\BasicAuthEasyApiToken;

final class BasicAuthEncoderTest extends AbstractTestCase
{
    /**
     * BasicAuthEncoder should encode successfully basic auth api token.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testBasicAuthEncodeSuccessfully(): void
    {
        $tests = [
            'username' => 'password',
            'username ' => ' password ',
            'username  ' => 'Sp3c|@l_cH\\aracters'
        ];

        foreach ($tests as $username => $password) {
            $token = (new BasicAuthEncoder())->encode(new BasicAuthEasyApiToken($username, $password));

            self::assertEquals(\base64_encode(\sprintf('%s:%s', $username, $password)), $token);
        }
    }

    /**
     * BasicAuthEncoder should throw exception if given token isn't instance of BasicAuthEasyApiTokenInterface.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new BasicAuthEncoder())->encode(new ApiKeyEasyApiToken(''));
    }

    /**
     * BasicAuthEncoder should throw exception if password empty in given token.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testUnableToEncodePasswordEmptyException(): void
    {
        $this->expectException(UnableToEncodeEasyApiTokenException::class);

        (new BasicAuthEncoder())->encode(new BasicAuthEasyApiToken('username', ''));
    }

    /**
     * BasicAuthEncoder should throw exception if username empty in given token.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testUnableToEncodeUsernameEmptyException(): void
    {
        $this->expectException(UnableToEncodeEasyApiTokenException::class);

        (new BasicAuthEncoder())->encode(new BasicAuthEasyApiToken('', 'password'));
    }
}

\class_alias(
    BasicAuthEncoderTest::class,
    'StepTheFkUp\EasyApiToken\Tests\Encoders\BasicAuthEncoderTest',
    false
);
