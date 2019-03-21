<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Encoders;

use StepTheFkUp\ApiToken\Decoders\JwtTokenDecoder;
use StepTheFkUp\ApiToken\Encoders\JwtTokenEncoder;
use StepTheFkUp\ApiToken\Exceptions\UnableToEncodeApiTokenException;
use StepTheFkUp\ApiToken\Tests\AbstractAuth0JwtTokenTestCase;
use StepTheFkUp\ApiToken\Tokens\BasicAuthApiToken;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;
use StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException;

final class Auth0JwtTokenEncoderTest extends AbstractAuth0JwtTokenTestCase
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
        $jwtDriver = $this->createAuth0JwtDriver();

        $tokenString = (new JwtTokenEncoder($jwtDriver))->encode(new JwtApiToken([]));
        $token = $this->createJwtTokenDecoder()->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $tokenString
        ]));

        self::assertInstanceOf(JwtApiToken::class, $token);

        $payload = $token->getPayload();

        foreach (static::$tokenPayload as $key => $value) {
            self::assertArrayHasKey($key, $payload);
            self::assertEquals($value, $payload[$key]);
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

        (new JwtTokenEncoder($this->createAuth0JwtDriver()))->encode(new BasicAuthApiToken('', ''));
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

        $jwtDriver = $this->createAuth0JwtDriver();

        (new JwtTokenEncoder($jwtDriver))->encode(new JwtApiToken(['scopes' => 1]));
    }

    /**
     * Create JwtTokenDecoder.
     *
     * @return \StepTheFkUp\ApiToken\Decoders\JwtTokenDecoder
     */
    private function createJwtTokenDecoder(): JwtTokenDecoder
    {
        return new JwtTokenDecoder($this->createJwtApiTokenFactory($this->createAuth0JwtDriver()));
    }
}
