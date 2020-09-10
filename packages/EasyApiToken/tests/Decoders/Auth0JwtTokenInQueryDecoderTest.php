<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Decoders;

use EonX\EasyApiToken\Decoders\JwtTokenInQueryDecoder;
use EonX\EasyApiToken\Tests\AbstractAuth0JwtTokenTestCase;
use EonX\EasyApiToken\Tokens\Jwt;

final class Auth0JwtTokenInQueryDecoderTest extends AbstractAuth0JwtTokenTestCase
{
    public function testJwtTokenDecodeSuccessfully(): void
    {
        $jwtEasyApiTokenFactory = $this->createJwtEasyApiTokenFactory($this->createAuth0JwtDriver());

        $decoder = new JwtTokenInQueryDecoder($jwtEasyApiTokenFactory, 'param');

        $request = $this->createRequest(null, [
            'param' => $this->createToken(),
        ]);

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtInterface $token */
        $token = $decoder->decode($request);

        $payload = $token->getPayload();

        self::assertInstanceOf(Jwt::class, $token);

        foreach (static::$tokenPayload as $key => $value) {
            self::assertArrayHasKey($key, $payload);
            self::assertEquals($value, $payload[$key]);
        }
    }

    public function testNullWhenQueryParamNotSet(): void
    {
        $decoder = new JwtTokenInQueryDecoder(
            $this->createJwtEasyApiTokenFactory($this->createAuth0JwtDriver()),
            'param'
        );

        self::assertNull($decoder->decode($this->createRequest()));
    }
}
