<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Encoders;

use StepTheFkUp\ApiToken\Decoders\JwtTokenDecoder;
use StepTheFkUp\ApiToken\Encoders\JwtTokenEncoder;
use StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException;
use StepTheFkUp\ApiToken\Tests\AbstractJwtTokenTestCase;
use StepTheFkUp\ApiToken\Tokens\BasicAuthApiToken;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;

final class JwtTokenEncoderTest extends AbstractJwtTokenTestCase
{
    /**
     * JwtTokenEncoder should encode tokens JwtTokenDecoder can decode.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException
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

            $tokenString = (new JwtTokenEncoder($jwtDriver))->encode(new JwtApiToken(static::$tokenPayload));
            $token = $this->createJwtTokenDecoder($algo, $publicKey)->decode($this->createServerRequest([
                'HTTP_AUTHORIZATION' => 'Bearer ' . $tokenString
            ]));

            self::assertInstanceOf(JwtApiToken::class, $token);

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
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException
     */
    public function testJwtTokenInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JwtTokenEncoder($this->createFirebaseJwtDriver()))->encode(new BasicAuthApiToken('', ''));
    }

    /**
     * JwtTokenEncoder should throw an exception if anything goes wrong while encoding token.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException
     */
    public function testJwtTokenUnableToEncodeException(): void
    {
        $this->expectException(UnableToEncodeApiTokenException::class);

        $jwtDriver = $this->createFirebaseJwtDriver('RS256', null, 'different-key');

        (new JwtTokenEncoder($jwtDriver))->encode(new JwtApiToken([]));
    }

    /**
     * Create JwtTokenDecoder.
     *
     * @param string $algo
     * @param string|resource $key
     *
     * @return \StepTheFkUp\ApiToken\Decoders\JwtTokenDecoder
     */
    private function createJwtTokenDecoder(string $algo, $key): JwtTokenDecoder
    {
        return new JwtTokenDecoder($this->createJwtApiTokenFactory(null, $key, null, [$algo]));
    }
}