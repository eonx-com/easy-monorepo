<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Encoders;

use EonX\EasyApiToken\Encoders\ApiKeyAsBasicAuthUsernameEncoder;
use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tokens\ApiKeyEasyApiToken;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;

final class ApiKeyAsBasicAuthUsernameEncoderTest extends AbstractTestCase
{
    /**
     * ApiKeyAsBasicAuthUsernameEncoder should throw exception if api key in given token is empty.
     *
     * @return void
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testEmptyApiKeyException(): void
    {
        $this->expectException(UnableToEncodeEasyApiTokenException::class);

        (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new ApiKeyEasyApiToken(''));
    }

    /**
     * ApiKeyAsBasicAuthUsernameEncoder should encode successfully api keys.
     *
     * @return void
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testApiKeyEncodeTokenSuccessfully(): void
    {
        $tests = [
            'apikey',
            'api-key',
            'Sp3c|@l_cH\\aracters'
        ];

        foreach ($tests as $test) {
            $token = (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new ApiKeyEasyApiToken($test));

            self::assertEquals(\base64_encode($test), $token);
        }
    }

    /**
     * ApiKeyAsBasicAuthUsernameEncoder should throw exception if given token isn't ApiKeyEasyApiTokenInterface.
     *
     * @return void
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new JwtEasyApiToken([], ''));
    }
}
