<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Tests\Encoders;

use StepTheFkUp\EasyApiToken\Decoders\JwtTokenDecoder;
use StepTheFkUp\EasyApiToken\Encoders\JwtTokenEncoder;
use StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use StepTheFkUp\EasyApiToken\Tests\AbstractFirebaseJwtTokenTestCase;
use StepTheFkUp\EasyApiToken\Tokens\BasicAuthEasyApiToken;
use StepTheFkUp\EasyApiToken\Tokens\JwtEasyApiToken;

final class FirebaseJwtTokenEncoderTest extends AbstractFirebaseJwtTokenTestCase
{
    /**
     * JwtTokenEncoder should encode tokens JwtTokenDecoder can decode.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
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
            /** @var \StepTheFkUp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */
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
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
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
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
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
     * @return \StepTheFkUp\EasyApiToken\Decoders\JwtTokenDecoder
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
    'LoyaltyCorp\EasyApiToken\Tests\Encoders\FirebaseJwtTokenEncoderTest',
    false
);
