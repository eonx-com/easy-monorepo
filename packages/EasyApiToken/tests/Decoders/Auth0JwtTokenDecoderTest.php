<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Decoders;

use EonX\EasyApiToken\Decoders\JwtTokenDecoder;
use EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException;
use EonX\EasyApiToken\Tests\AbstractAuth0JwtTokenTestCase;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;

final class Auth0JwtTokenDecoderTest extends AbstractAuth0JwtTokenTestCase
{
    public function testJwtTokenDecodeSuccessfully(): void
    {
        $jwtEasyApiTokenFactory = $this->createJwtEasyApiTokenFactory($this->createAuth0JwtDriver());

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */
        $token = (new JwtTokenDecoder($jwtEasyApiTokenFactory))->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken(),
        ]));

        $payload = $token->getPayload();

        self::assertInstanceOf(JwtEasyApiToken::class, $token);

        foreach (static::$tokenPayload as $key => $value) {
            self::assertArrayHasKey($key, $payload);
            self::assertEquals($value, $payload[$key]);
        }
    }

    public function testJwtTokenNullIfAuthorizationHeaderNotSet(): void
    {
        $decoder = new JwtTokenDecoder($this->createJwtEasyApiTokenFactory($this->createAuth0JwtDriver()));

        self::assertNull($decoder->decode($this->createServerRequest()));
    }

    public function testJwtTokenNullIfDoesntStartWithBearer(): void
    {
        $decoder = new JwtTokenDecoder($this->createJwtEasyApiTokenFactory($this->createAuth0JwtDriver()));

        self::assertNull($decoder->decode($this->createServerRequest(['HTTP_AUTHORIZATION' => 'SomethingElse'])));
    }

    public function testJwtTokenThrowExceptionIfUnableToDecodeToken(): void
    {
        $this->expectException(InvalidEasyApiTokenFromRequestException::class);

        $jwtEasyApiTokenFactory = $this->createJwtEasyApiTokenFactory($this->createAuth0JwtDriver());

        (new JwtTokenDecoder($jwtEasyApiTokenFactory))->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Bearer WeirdTokenHere',
        ]));
    }
}
