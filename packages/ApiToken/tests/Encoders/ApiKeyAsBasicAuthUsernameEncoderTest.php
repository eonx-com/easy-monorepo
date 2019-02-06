<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Encoders;

use StepTheFkUp\ApiToken\Encoders\ApiKeyAsBasicAuthUsernameEncoder;
use StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException;
use StepTheFkUp\ApiToken\Tests\AbstractTestCase;
use StepTheFkUp\ApiToken\Tokens\ApiKeyApiToken;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;

final class ApiKeyAsBasicAuthUsernameEncoderTest extends AbstractTestCase
{
    /**
     * ApiKeyAsBasicAuthUsernameEncoder should throw exception if api key in given token is empty.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException
     */
    public function testEmptyApiKeyException(): void
    {
        $this->expectException(UnableToEncodeApiTokenException::class);

        (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new ApiKeyApiToken(''));
    }

    /**
     * ApiKeyAsBasicAuthUsernameEncoder should encode successfully api keys.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException
     */
    public function testApiKeyEncodeTokenSuccessfully(): void
    {
        $tests = [
            'apikey',
            'api-key',
            'Sp3c|@l_cH\\aracters'
        ];

        foreach ($tests as $test) {
            $token = (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new ApiKeyApiToken($test));

            self::assertEquals(\base64_encode($test), $token);
        }
    }

    /**
     * ApiKeyAsBasicAuthUsernameEncoder should throw exception if given token isn't ApiKeyApiTokenInterface.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException
     */
    public function testInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new JwtApiToken([]));
    }
}