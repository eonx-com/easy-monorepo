<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Encoders;

use LoyaltyCorp\EasyApiToken\Decoders\JwtTokenDecoder;
use LoyaltyCorp\EasyApiToken\Encoders\JwtTokenEncoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException;
use LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use LoyaltyCorp\EasyApiToken\Tests\AbstractFirebaseJwtTokenTestCase;
use LoyaltyCorp\EasyApiToken\Tokens\BasicAuthEasyApiToken;
use LoyaltyCorp\EasyApiToken\Tokens\JwtEasyApiToken;

final class FirebaseJwtTokenEncoderTest extends AbstractFirebaseJwtTokenTestCase
{
    /**
     * JwtTokenEncoder should encode tokens JwtTokenDecoder can decode.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testJwtTokenEncodeSuccessfully(): void
    {
        foreach (static::$algos as $algo) {
            $privateKey = $publicKey = static::$key;

            if ($this->isAlgoRs($algo)) {
                $privateKey = $this->getOpenSslPrivateKey();
                $publicKey = $this->getOpenSslPublicKey();
            }

            $jwtDriver = $this->createFirebaseJwtDriver($algo, null, $privateKey);

            $tokenString = (new JwtTokenEncoder($jwtDriver))->encode(new JwtEasyApiToken(static::$tokenPayload));
            /** @var \LoyaltyCorp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */
            $token = $this->createJwtTokenDecoder($algo, $publicKey)->decode($this->createServerRequest([
                'HTTP_AUTHORIZATION' => 'Bearer ' . $tokenString
            ]));

            self::assertInstanceOf(JwtEasyApiToken::class, $token);

            $payload = $token->getPayload();

            foreach (static::$tokenPayload as $key => $value) {
                self::assertArrayHasKey($key, $payload);
                self::assertEquals($value, $payload[$key]);
            }
        }
    }

    /**
     * JwtTokenEncoder should throw an exception if given token isn't a JWT token.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testJwtTokenInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JwtTokenEncoder($this->createFirebaseJwtDriver()))->encode(new BasicAuthEasyApiToken('', ''));
    }

    /**
     * JwtTokenEncoder should throw an exception if anything goes wrong while encoding token.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testJwtTokenUnableToEncodeException(): void
    {
        $this->expectException(UnableToEncodeEasyApiTokenException::class);

        $jwtDriver = $this->createFirebaseJwtDriver('RS256', null, 'different-key');

        (new JwtTokenEncoder($jwtDriver))->encode(new JwtEasyApiToken([]));
    }

    /**
     * Create JwtTokenDecoder.
     *
     * @param string $algo
     * @param string|resource $key
     *
     * @return \LoyaltyCorp\EasyApiToken\Decoders\JwtTokenDecoder
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

\class_alias(
    FirebaseJwtTokenEncoderTest::class,
    'StepTheFkUp\EasyApiToken\Tests\Encoders\FirebaseJwtTokenEncoderTest',
    false
);
