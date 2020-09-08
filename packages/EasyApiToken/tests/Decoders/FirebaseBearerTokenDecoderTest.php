<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Decoders;

use EonX\EasyApiToken\Decoders\BearerTokenDecoder;
use EonX\EasyApiToken\Tests\AbstractFirebaseJwtTokenTestCase;
use EonX\EasyApiToken\Tokens\Jwt;

final class FirebaseBearerTokenDecoderTest extends AbstractFirebaseJwtTokenTestCase
{
    public function testJwtTokenDecodeSuccessfully(): void
    {
        foreach (static::$algos as $algo) {
            $key = static::$key;

            if ($this->isAlgoRs($algo)) {
                $key = $this->getOpenSslPublicKey();
            }

            $jwtDriver = $this->createFirebaseJwtDriver(null, $key, null, [$algo]);

            /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtInterface $token */
            $token = (new BearerTokenDecoder($jwtDriver))->decode($this->createRequest([
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
        $decoder = new BearerTokenDecoder($this->createFirebaseJwtDriver());

        self::assertNull($decoder->decode($this->createRequest()));
    }

    public function testJwtTokenNullIfDoesntStartWithBearer(): void
    {
        $decoder = new BearerTokenDecoder($this->createFirebaseJwtDriver());

        self::assertNull($decoder->decode($this->createRequest(['HTTP_AUTHORIZATION' => 'SomethingElse'])));
    }

    public function testJwtTokenReturnNullIfUnableToDecodeToken(): void
    {
        $jwtDriver = $this->createFirebaseJwtDriver(null, 'different-key', null, ['HS256'], 2);

        $token = (new BearerTokenDecoder($jwtDriver))->decode($this->createRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken(),
        ]));

        self::assertNull($token);
    }
}
