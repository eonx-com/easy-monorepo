<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Decoders;

use EoneoPay\Utils\Str;
use Firebase\JWT\JWT;
use StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException;
use StepTheFkUp\ApiToken\Decoders\JwtTokenDecoder;
use StepTheFkUp\ApiToken\Tests\AbstractTestCase;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;

final class JwtTokenDecoderTest extends AbstractTestCase
{
    /**
     * @var string[]
     */
    private static $algos = [
        'HS256',
        'HS512',
        'HS384',
        'RS256',
        'RS384',
        'RS512'
    ];

    /**
     * @var string
     */
    private static $defaultAlgo = 'HS256';

    /**
     * @var string
     */
    private static $key = 'key';

    /**
     * @var mixed[]
     */
    private static $tokenPayload = [
        'iss' => 'stepthefkup.com',
        'aud' => 'stepthefkup.com.au',
        'iat' => 1549340373,
        'nbf' => 1549340373
    ];

    /**
     * JwtTokenDecoder should return null if Authorization header not set.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function testJwtTokenNullIfAuthorizationHeaderNotSet(): void
    {
        self::assertNull((new JwtTokenDecoder([], static::$key))->decode($this->createServerRequest()));
    }

    /**
     * JwtTokenDecoder should return null if Authorization header doesn't start with "Bearer ".
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function testJwtTokenNullIfDoesntStartWithBearer(): void
    {
        self::assertNull((new JwtTokenDecoder([], static::$key))->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'SomethingElse'
        ])));
    }

    /**
     * JwtTokenDecoder should throw an exception if unable to decode token because token is invalid.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function testJwtTokenThrowExceptionIfUnableTodecodeToken(): void
    {
        $this->expectException(InvalidApiTokenFromRequestException::class);

        (new JwtTokenDecoder(['HS256'], 'different-key', 2))->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken()
        ]));
    }

    /**
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function testJwtTokenDecodeSuccessfully(): void
    {
        foreach (static::$algos as $algo) {
            $key = static::$key;

            if ((new Str())->startsWith($algo, 'RS')) {
                $key = $this->getOpenSslPublicKey();
            }

            $token = (new JwtTokenDecoder([$algo], $key))->decode($this->createServerRequest([
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken($algo)
            ]));

            $payload = $token->getPayload();

            self::assertInstanceOf(JwtApiToken::class, $token);

            foreach (static::$tokenPayload as $key => $value) {
                self::assertArrayHasKey($key, $payload);
                self::assertEquals($value, $payload[$key]);
            }
        }
    }

    /**
     * Create JWT token for given algo.
     *
     * @param null|string $algo
     *
     * @return string
     */
    private function createToken(?string $algo = null): string
    {
        $key = static::$key;

        if ($algo !== null && (new Str())->startsWith($algo, 'RS')) {
            $key = $this->getOpenSslPrivateKey();
        }

        return JWT::encode(static::$tokenPayload, $key, $algo ?? static::$defaultAlgo);
    }

    /**
     * Get the openssl private key for algorithms using it.
     *
     * @return bool|resource
     */
    private function getOpenSslPrivateKey()
    {
        return \openssl_pkey_get_private(\sprintf('file://%s', __DIR__ . '/../keys/jwt-private.pem'));
    }

    /**
     * Get the openssl public key for algorithms using it.
     *
     * @return resource
     */
    private function getOpenSslPublicKey()
    {
        return \openssl_pkey_get_public(\sprintf('file://%s', __DIR__ . '/../keys/jwt-public.pem'));
    }
}