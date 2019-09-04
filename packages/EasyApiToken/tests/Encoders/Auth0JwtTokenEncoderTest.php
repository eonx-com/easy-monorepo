<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Encoders;

use LoyaltyCorp\EasyApiToken\Decoders\JwtTokenDecoder;
use LoyaltyCorp\EasyApiToken\Encoders\JwtTokenEncoder;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException;
use LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use LoyaltyCorp\EasyApiToken\Tests\AbstractAuth0JwtTokenTestCase;
use LoyaltyCorp\EasyApiToken\Tokens\BasicAuthEasyApiToken;
use LoyaltyCorp\EasyApiToken\Tokens\JwtEasyApiToken;

final class Auth0JwtTokenEncoderTest extends AbstractAuth0JwtTokenTestCase
{
    /**
     * Test creating a token with roles.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    public function testCreateTokenWithRoles(): void
    {
        $jwtDriver = $this->createAuth0JwtDriver();
        $encoder = new JwtTokenEncoder($jwtDriver);
        $apiToken = new JwtEasyApiToken([
            'roles' => [
                'https://manage.eonx.com/roles' => [
                    'subscriptions:operator',
                    'subscriptions:finance'
                ]
            ]
        ]);
        $token = $encoder->encode($apiToken);

        /** @var \LoyaltyCorp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $decodedToken */
        $decodedToken = $this->createJwtTokenDecoder()->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]));

        self::assertInstanceOf(JwtEasyApiToken::class, $decodedToken);
        self::assertTrue($decodedToken->hasClaim('https://manage.eonx.com/roles'));
        self::assertSame(
            [
                'subscriptions:operator',
                'subscriptions:finance'
            ],
            $decodedToken->getClaim('https://manage.eonx.com/roles')
        );
    }

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
        $jwtDriver = $this->createAuth0JwtDriver();

        $tokenString = (new JwtTokenEncoder($jwtDriver))->encode(new JwtEasyApiToken([]));
        /** @var \LoyaltyCorp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */
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
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
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
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException
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
     * @return \LoyaltyCorp\EasyApiToken\Decoders\JwtTokenDecoder
     */
    private function createJwtTokenDecoder(): JwtTokenDecoder
    {
        return new JwtTokenDecoder($this->createJwtEasyApiTokenFactory($this->createAuth0JwtDriver()));
    }
}

\class_alias(
    Auth0JwtTokenEncoderTest::class,
    'StepTheFkUp\EasyApiToken\Tests\Encoders\Auth0JwtTokenEncoderTest',
    false
);
