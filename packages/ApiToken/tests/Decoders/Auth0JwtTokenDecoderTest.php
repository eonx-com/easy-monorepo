<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Decoders;

use StepTheFkUp\ApiToken\Decoders\JwtTokenDecoder;
use StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException;
use StepTheFkUp\ApiToken\Tests\AbstractAuth0JwtTokenTestCase;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;

final class Auth0JwtTokenDecoderTest extends AbstractAuth0JwtTokenTestCase
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

        $token = (new JwtTokenDecoder($jwtApiTokenFactory))->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->createToken()
        ]));

        $payload = $token->getPayload();

        self::assertInstanceOf(JwtApiToken::class, $token);

        foreach (static::$tokenPayload as $key => $value) {
            self::assertArrayHasKey($key, $payload);
            self::assertEquals($value, $payload[$key]);
        }
    }

    /**
     * JwtTokenDecoder should return null if Authorization header not set.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function testJwtTokenNullIfAuthorizationHeaderNotSet(): void
    {
        self::assertNull((new JwtTokenDecoder($this->createJwtApiTokenFactory($this->createAuth0JwtDriver())))->decode($this->createServerRequest()));
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
        self::assertNull((new JwtTokenDecoder($this->createJwtApiTokenFactory($this->createAuth0JwtDriver())))->decode($this->createServerRequest([
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
    public function testJwtTokenThrowExceptionIfUnableToDecodeToken(): void
    {
        $this->expectException(InvalidApiTokenFromRequestException::class);

        $jwtApiTokenFactory = $this->createJwtApiTokenFactory($this->createAuth0JwtDriver());

        (new JwtTokenDecoder($jwtApiTokenFactory))->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Bearer WeirdTokenHere'
        ]));
    }
}
