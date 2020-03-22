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
    public function testEmptyApiKeyException(): void
    {
        $this->expectException(UnableToEncodeEasyApiTokenException::class);

        (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new ApiKeyEasyApiToken(''));
    }

    public function testApiKeyEncodeTokenSuccessfully(): void
    {
        $tests = [
            'apikey',
            'api-key',
            'Sp3c|@l_cH\\aracters',
        ];

        foreach ($tests as $test) {
            $token = (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new ApiKeyEasyApiToken($test));

            self::assertEquals(\base64_encode($test), $token);
        }
    }

    public function testInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ApiKeyAsBasicAuthUsernameEncoder())->encode(new JwtEasyApiToken([], ''));
    }
}
