<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\Decoder;

use EonX\EasyApiToken\Common\Decoder\BearerTokenDecoder;
use EonX\EasyApiToken\Common\ValueObject\JwtToken;

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

            /** @var \EonX\EasyApiToken\Common\ValueObject\JwtToken $jwtToken */
            $jwtToken = (new BearerTokenDecoder($jwtDriver))->decode($this->createRequest([
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken($algo),
            ]));

            $payload = $jwtToken->getPayload();

            self::assertInstanceOf(JwtToken::class, $jwtToken);

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
