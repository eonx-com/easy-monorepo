<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Encoders;

use StepTheFkUp\ApiToken\Encoders\BasicAuthEncoder;
use StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException;
use StepTheFkUp\ApiToken\Tests\AbstractTestCase;
use StepTheFkUp\ApiToken\Tokens\ApiKeyApiToken;
use StepTheFkUp\ApiToken\Tokens\BasicAuthApiToken;

final class BasicAuthEncoderTest extends AbstractTestCase
{
    /**
     * BasicAuthEncoder should encode successfully basic auth api token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException
     */
    public function testBasicAuthEncodeSuccessfully(): void
    {
        $tests = [
            'username' => 'password',
            'username ' => ' password ',
            'username  ' => 'Sp3c|@l_cH\\aracters'
        ];

        foreach ($tests as $username => $password) {
            $token = (new BasicAuthEncoder())->encode(new BasicAuthApiToken($username, $password));

            self::assertEquals(\base64_encode(\sprintf('%s:%s', $username, $password)), $token);
        }
    }

    /**
     * BasicAuthEncoder should throw exception if given token isn't instance of BasicAuthApiTokenInterface.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException
     */
    public function testInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new BasicAuthEncoder())->encode(new ApiKeyApiToken(''));
    }

    /**
     * BasicAuthEncoder should throw exception if password empty in given token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException
     */
    public function testUnableToEncodePasswordEmptyException(): void
    {
        $this->expectException(UnableToEncodeApiTokenException::class);

        (new BasicAuthEncoder())->encode(new BasicAuthApiToken('username', ''));
    }

    /**
     * BasicAuthEncoder should throw exception if username empty in given token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException
     */
    public function testUnableToEncodeUsernameEmptyException(): void
    {
        $this->expectException(UnableToEncodeApiTokenException::class);

        (new BasicAuthEncoder())->encode(new BasicAuthApiToken('', 'password'));
    }
}
