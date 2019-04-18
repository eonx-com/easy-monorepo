<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Tests\Decoders;

use StepTheFkUp\EasyApiToken\Decoders\JwtTokenInQueryDecoder;
use StepTheFkUp\EasyApiToken\Tests\AbstractAuth0JwtTokenTestCase;
use StepTheFkUp\EasyApiToken\Tokens\JwtEasyApiToken;

final class Auth0JwtTokenInQueryDecoderTest extends AbstractAuth0JwtTokenTestCase
{
    /**
     * JwtTokenDecoder should decode token successfully for each algorithms.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    public function testJwtTokenDecodeSuccessfully(): void
    {
        $jwtEasyApiTokenFactory = $this->createJwtEasyApiTokenFactory($this->createAuth0JwtDriver());

        $decoder = new JwtTokenInQueryDecoder($jwtEasyApiTokenFactory, 'param');

        $request = $this->createServerRequest(null, [
            'param' => $this->createToken()
        ]);

        /** @var \StepTheFkUp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */
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
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
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
    'LoyaltyCorp\EasyApiToken\Tests\Decoders\Auth0JwtTokenInQueryDecoderTest',
    false
);
