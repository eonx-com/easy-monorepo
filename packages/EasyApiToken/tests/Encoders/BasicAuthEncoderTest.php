<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Tests\Encoders;

use StepTheFkUp\EasyApiToken\Encoders\BasicAuthEncoder;
use StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use StepTheFkUp\EasyApiToken\Tests\AbstractTestCase;
use StepTheFkUp\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use StepTheFkUp\EasyApiToken\Tokens\BasicAuthEasyApiToken;

final class BasicAuthEncoderTest extends AbstractTestCase
{
    /**
     * BasicAuthEncoder should encode successfully basic auth api token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
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
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
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
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
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
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testUnableToEncodeUsernameEmptyException(): void
    {
        $this->expectException(UnableToEncodeEasyApiTokenException::class);

        (new BasicAuthEncoder())->encode(new BasicAuthEasyApiToken('', 'password'));
    }
}

\class_alias(
    BasicAuthEncoderTest::class,
    'LoyaltyCorp\EasyApiToken\Tests\Encoders\BasicAuthEncoderTest',
    false
);
