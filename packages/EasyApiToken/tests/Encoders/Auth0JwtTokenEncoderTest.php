<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Tests\Encoders;

use StepTheFkUp\EasyApiToken\Decoders\JwtTokenDecoder;
use StepTheFkUp\EasyApiToken\Encoders\JwtTokenEncoder;
use StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use StepTheFkUp\EasyApiToken\Tests\AbstractAuth0JwtTokenTestCase;
use StepTheFkUp\EasyApiToken\Tokens\BasicAuthEasyApiToken;
use StepTheFkUp\EasyApiToken\Tokens\JwtEasyApiToken;

final class Auth0JwtTokenEncoderTest extends AbstractAuth0JwtTokenTestCase
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
        $jwtDriver = $this->createAuth0JwtDriver();

        $tokenString = (new JwtTokenEncoder($jwtDriver))->encode(new JwtEasyApiToken([]));
        /** @var \StepTheFkUp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */
        $token = $this->createJwtTokenDecoder()->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $tokenString
        ]));

        self::assertInstanceOf(JwtEasyApiToken::class, $token);

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
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     */
    public function testJwtTokenInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JwtTokenEncoder($this->createAuth0JwtDriver()))->encode(new BasicAuthEasyApiToken('', ''));
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

        $jwtDriver = $this->createAuth0JwtDriver();

        (new JwtTokenEncoder($jwtDriver))->encode(new JwtEasyApiToken(['scopes' => 1]));
    }

    /**
     * Create JwtTokenDecoder.
     *
     * @return \StepTheFkUp\EasyApiToken\Decoders\JwtTokenDecoder
     */
    private function createJwtTokenDecoder(): JwtTokenDecoder
    {
        return new JwtTokenDecoder($this->createJwtEasyApiTokenFactory($this->createAuth0JwtDriver()));
    }
}

\class_alias(
    Auth0JwtTokenEncoderTest::class,
    'LoyaltyCorp\EasyApiToken\Tests\Encoders\Auth0JwtTokenEncoderTest',
    false
);
