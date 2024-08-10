<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\Decoder;

use EonX\EasyApiToken\Common\Decoder\BearerTokenDecoder;
use EonX\EasyApiToken\Common\ValueObject\JwtToken;

final class BearerTokenDecoderTest extends AbstractAuth0JwtTokenTestCase
{
    public function testJwtTokenDecodeSuccessfully(): void
    {
        $jwtDriver = $this->createAuth0JwtDriver();

        /** @var \EonX\EasyApiToken\Common\ValueObject\JwtToken $jwtToken */
        $jwtToken = (new BearerTokenDecoder($jwtDriver))->decode($this->createRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken(),
        ]));

        $payload = $jwtToken->getPayload();

        self::assertInstanceOf(JwtToken::class, $jwtToken);

        foreach (static::$tokenPayload as $key => $value) {
            self::assertArrayHasKey($key, $payload);
            self::assertEquals($value, $payload[$key]);
        }
    }

    public function testJwtTokenNullIfAuthorizationHeaderNotSet(): void
    {
        $decoder = new BearerTokenDecoder($this->createAuth0JwtDriver());

        self::assertNull($decoder->decode($this->createRequest()));
    }

    public function testJwtTokenNullIfDoesntStartWithBearer(): void
    {
        $decoder = new BearerTokenDecoder($this->createAuth0JwtDriver());

        self::assertNull($decoder->decode($this->createRequest([
            'HTTP_AUTHORIZATION' => 'SomethingElse',
        ])));
    }

    public function testJwtTokenReturnNullIfUnableToDecodeToken(): void
    {
        $jwtDriver = $this->createAuth0JwtDriver();

        $token = (new BearerTokenDecoder($jwtDriver))->decode($this->createRequest([
            'HTTP_AUTHORIZATION' => 'Bearer WeirdTokenHere',
        ]));

        self::assertNull($token);
    }
}
