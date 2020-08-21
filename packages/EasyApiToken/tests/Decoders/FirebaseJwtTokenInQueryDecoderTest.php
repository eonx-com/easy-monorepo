<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Decoders;

use EonX\EasyApiToken\Decoders\JwtTokenInQueryDecoder;
use EonX\EasyApiToken\Tests\AbstractFirebaseJwtTokenTestCase;
use EonX\EasyApiToken\Tokens\Jwt;

final class FirebaseJwtTokenInQueryDecoderTest extends AbstractFirebaseJwtTokenTestCase
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
            $decoder = new JwtTokenInQueryDecoder($jwtEasyApiTokenFactory, 'param');
            $request = $this->createRequest(null, [
                'param' => $this->createToken($algo),
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
    }

    public function testNullWhenQueryParamNotSet(): void
    {
        $decoder = new JwtTokenInQueryDecoder(
            $this->createJwtEasyApiTokenFactory(
                $this->createFirebaseJwtDriver()
            ),
            'param'
        );

        self::assertNull($decoder->decode($this->createRequest()));
    }
}
