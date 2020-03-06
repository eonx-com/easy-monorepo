<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Encoders;

use EonX\EasyApiToken\Decoders\JwtTokenDecoder;
use EonX\EasyApiToken\Encoders\JwtTokenEncoder;
use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use EonX\EasyApiToken\Tests\AbstractFirebaseJwtTokenTestCase;
use EonX\EasyApiToken\Tokens\BasicAuthEasyApiToken;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;

final class FirebaseJwtTokenEncoderTest extends AbstractFirebaseJwtTokenTestCase
{
    public function testJwtTokenEncodeSuccessfully(): void
    {
        foreach (static::$algos as $algo) {
            $privateKey = $publicKey = static::$key;

            if ($this->isAlgoRs($algo)) {
                $privateKey = $this->getOpenSslPrivateKey();
                $publicKey = $this->getOpenSslPublicKey();
            }

            $jwtDriver = $this->createFirebaseJwtDriver($algo, null, $privateKey);

            $tokenString = (new JwtTokenEncoder($jwtDriver))->encode(new JwtEasyApiToken(static::$tokenPayload, ''));
            /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */
            $token = $this->createJwtTokenDecoder($algo, $publicKey)->decode($this->createServerRequest([
                'HTTP_AUTHORIZATION' => 'Bearer ' . $tokenString,
            ]));

            self::assertInstanceOf(JwtEasyApiToken::class, $token);

            $payload = $token->getPayload();

            foreach (static::$tokenPayload as $key => $value) {
                self::assertArrayHasKey($key, $payload);
                self::assertEquals($value, $payload[$key]);
            }
        }
    }

    public function testJwtTokenInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JwtTokenEncoder($this->createFirebaseJwtDriver()))->encode(new BasicAuthEasyApiToken('', '', ''));
    }

    public function testJwtTokenUnableToEncodeException(): void
    {
        $this->expectException(UnableToEncodeEasyApiTokenException::class);

        $jwtDriver = $this->createFirebaseJwtDriver('RS256', null, 'different-key');

        (new JwtTokenEncoder($jwtDriver))->encode(new JwtEasyApiToken([], ''));
    }

    /**
     * @param null|string|resource $key
     */
    private function createJwtTokenDecoder(string $algo, $key): JwtTokenDecoder
    {
        return new JwtTokenDecoder($this->createJwtEasyApiTokenFactory($this->createFirebaseJwtDriver(
            null,
            $key,
            null,
            [$algo]
        )));
    }
}
