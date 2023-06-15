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
        foreach (self::$algos as $algo) {
            $key = self::$key;

            if ($this->isAlgoRs($algo)) {
                $key = $this->getOpenSslPublicKey();
                $key = $key === false ? null : $key;
            }

            $jwtDriver = $this->createFirebaseJwtDriver($algo, $key);

            /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtInterface $token */
            $token = (new BearerTokenDecoder($jwtDriver))->decode($this->createRequest([
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken($algo),
            ]));

            $payload = $token->getPayload();

            self::assertInstanceOf(Jwt::class, $token);

            foreach (self::$tokenPayload as $key => $value) {
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

        self::assertNull($decoder->decode($this->createRequest([
            'HTTP_AUTHORIZATION' => 'SomethingElse',
        ])));
    }

    public function testJwtTokenReturnNullIfUnableToDecodeToken(): void
    {
        $jwtDriver = $this->createFirebaseJwtDriver(null, 'different-key', null, 2);

        $token = (new BearerTokenDecoder($jwtDriver))->decode($this->createRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken(),
        ]));

        self::assertNull($token);
    }
}
