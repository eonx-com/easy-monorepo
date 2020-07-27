<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Decoders;

use EonX\EasyApiToken\Decoders\JwtTokenDecoder;
use EonX\EasyApiToken\Tests\AbstractFirebaseJwtTokenTestCase;
use EonX\EasyApiToken\Tokens\Jwt;

final class FirebaseJwtTokenDecoderTest extends AbstractFirebaseJwtTokenTestCase
{
    public function testJwtTokenDecodeSuccessfully(): void
    {
        foreach (static::$algos as $algo) {
            $key = static::$key;

            if ($this->isAlgoRs($algo)) {
                $key = $this->getOpenSslPublicKey();
            }

            $jwtEasyApiTokenFactory = $this->createJwtEasyApiTokenFactory($this->createFirebaseJwtDriver(
                null,
                $key,
                null,
                [$algo]
            ));

            /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtInterface $token */
            $token = (new JwtTokenDecoder($jwtEasyApiTokenFactory))->decode($this->createServerRequest([
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken($algo),
            ]));

            $payload = $token->getPayload();

            self::assertInstanceOf(Jwt::class, $token);

            foreach (static::$tokenPayload as $key => $value) {
                self::assertArrayHasKey($key, $payload);
                self::assertEquals($value, $payload[$key]);
            }
        }
    }

    public function testJwtTokenNullIfAuthorizationHeaderNotSet(): void
    {
        $decoder = new JwtTokenDecoder($this->createJwtEasyApiTokenFactory($this->createFirebaseJwtDriver()));

        self::assertNull($decoder->decode($this->createServerRequest()));
    }

    public function testJwtTokenNullIfDoesntStartWithBearer(): void
    {
        $decoder = new JwtTokenDecoder($this->createJwtEasyApiTokenFactory($this->createFirebaseJwtDriver()));

        self::assertNull($decoder->decode($this->createServerRequest(['HTTP_AUTHORIZATION' => 'SomethingElse'])));
    }

    public function testJwtTokenReturnNullIfUnableToDecodeToken(): void
    {
        $jwtEasyApiTokenFactory = $this->createJwtEasyApiTokenFactory($this->createFirebaseJwtDriver(
            null,
            'different-key',
            null,
            ['HS256'],
            2
        ));

        $token = (new JwtTokenDecoder($jwtEasyApiTokenFactory))->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken(),
        ]));

        self::assertNull($token);
    }
}
