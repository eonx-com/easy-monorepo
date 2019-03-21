<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Decoders;

use StepTheFkUp\ApiToken\Decoders\JwtTokenInQueryDecoder;
use StepTheFkUp\ApiToken\Tests\AbstractAuth0JwtTokenTestCase;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;

final class Auth0JwtTokenInQueryDecoderTest extends AbstractAuth0JwtTokenTestCase
{
    /**
     * JwtTokenDecoder should decode token successfully for each algorithms.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function testJwtTokenDecodeSuccessfully(): void
    {
        $jwtApiTokenFactory = $this->createJwtApiTokenFactory($this->createAuth0JwtDriver());

        $decoder = new JwtTokenInQueryDecoder($jwtApiTokenFactory, 'param');

        $request = $this->createServerRequest(null, [
            'param' => $this->createToken()
        ]);

        $token = $decoder->decode($request);

        $payload = $token->getPayload();

        self::assertInstanceOf(JwtApiToken::class, $token);

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
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function testNullWhenQueryParamNotSet(): void
    {
        $decoder = new JwtTokenInQueryDecoder($this->createJwtApiTokenFactory(
            $this->createAuth0JwtDriver()),
            'param'
        );

        self::assertNull($decoder->decode($this->createServerRequest()));
    }
}
