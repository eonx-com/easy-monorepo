<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\Decoder;

use EonX\EasyApiToken\Common\Decoder\BearerTokenDecoder;
use EonX\EasyApiToken\Common\ValueObject\Jwt;

final class BearerTokenDecoderTest extends AbstractAuth0JwtTokenTestCase
{
    public function testJwtTokenDecodeSuccessfully(): void
    {
        $jwtDriver = $this->createAuth0JwtDriver();

        /** @var \EonX\EasyApiToken\Common\ValueObject\JwtInterface $token */
        $token = (new BearerTokenDecoder($jwtDriver))->decode($this->createRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken(),
        ]));

        $payload = $token->getPayload();

        self::assertInstanceOf(Jwt::class, $token);

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
