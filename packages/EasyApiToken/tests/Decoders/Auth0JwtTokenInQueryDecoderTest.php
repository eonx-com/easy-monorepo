<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Decoders;

use LoyaltyCorp\EasyApiToken\Decoders\JwtTokenInQueryDecoder;
use LoyaltyCorp\EasyApiToken\Tests\AbstractAuth0JwtTokenTestCase;
use LoyaltyCorp\EasyApiToken\Tokens\JwtEasyApiToken;

final class Auth0JwtTokenInQueryDecoderTest extends AbstractAuth0JwtTokenTestCase
{
    /**
     * JwtTokenDecoder should decode token successfully for each algorithms.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    public function testJwtTokenDecodeSuccessfully(): void
    {
        $jwtEasyApiTokenFactory = $this->createJwtEasyApiTokenFactory($this->createAuth0JwtDriver());

        $decoder = new JwtTokenInQueryDecoder($jwtEasyApiTokenFactory, 'param');

        $request = $this->createServerRequest(null, [
            'param' => $this->createToken()
        ]);

        /** @var \LoyaltyCorp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */
        $token = $decoder->decode($request);

        $payload = $token->getPayload();

        self::assertInstanceOf(JwtEasyApiToken::class, $token);

        foreach (static::$tokenPayload as $key => $value) {
            self::assertArrayHasKey($key, $payload);
            self::assertEquals($value, $payload[$key]);
        }
    }

    /**
     * JwtTokenInQueryDecoder should return null if query param not set on request.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    public function testNullWhenQueryParamNotSet(): void
    {
        $decoder = new JwtTokenInQueryDecoder(
            $this->createJwtEasyApiTokenFactory(
                $this->createAuth0JwtDriver()
            ),
            'param'
        );

        self::assertNull($decoder->decode($this->createServerRequest()));
    }
}

\class_alias(
    Auth0JwtTokenInQueryDecoderTest::class,
    'StepTheFkUp\EasyApiToken\Tests\Decoders\Auth0JwtTokenInQueryDecoderTest',
    false
);
