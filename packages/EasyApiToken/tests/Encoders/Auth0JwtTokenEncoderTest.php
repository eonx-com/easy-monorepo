<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Encoders;

use EonX\EasyApiToken\Decoders\JwtTokenDecoder;
use EonX\EasyApiToken\Encoders\JwtTokenEncoder;
use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use EonX\EasyApiToken\Tests\AbstractAuth0JwtTokenTestCase;
use EonX\EasyApiToken\Tokens\BasicAuthEasyApiToken;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;

final class Auth0JwtTokenEncoderTest extends AbstractAuth0JwtTokenTestCase
{
    public function testCreateTokenWithRoles(): void
    {
        $jwtDriver = $this->createAuth0JwtDriver();
        $encoder = new JwtTokenEncoder($jwtDriver);
        $apiToken = new JwtEasyApiToken([
            'roles' => [
                'https://manage.eonx.com/roles' => [
                    'subscriptions:operator',
                    'subscriptions:finance',
                ],
            ],
        ], 'original');
        $token = $encoder->encode($apiToken);

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $decodedToken */
        $decodedToken = $this->createJwtTokenDecoder()->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]));

        self::assertInstanceOf(JwtEasyApiToken::class, $decodedToken);
        self::assertTrue($decodedToken->hasClaim('https://manage.eonx.com/roles'));
        self::assertEquals(
            [
                'subscriptions:operator',
                'subscriptions:finance',
            ],
            $decodedToken->getClaim('https://manage.eonx.com/roles')
        );
    }

    public function testJwtTokenEncodeSuccessfully(): void
    {
        $jwtDriver = $this->createAuth0JwtDriver();

        $tokenString = (new JwtTokenEncoder($jwtDriver))->encode(new JwtEasyApiToken([], ''));
        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */
        $token = $this->createJwtTokenDecoder()->decode($this->createServerRequest([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $tokenString,
        ]));

        self::assertInstanceOf(JwtEasyApiToken::class, $token);

        $payload = $token->getPayload();

        foreach (static::$tokenPayload as $key => $value) {
            self::assertArrayHasKey($key, $payload);
            self::assertEquals($value, $payload[$key]);
        }
    }

    public function testJwtTokenInvalidTokenException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JwtTokenEncoder($this->createAuth0JwtDriver()))->encode(new BasicAuthEasyApiToken('', '', ''));
    }

    public function testJwtTokenUnableToEncodeException(): void
    {
        $this->expectException(UnableToEncodeEasyApiTokenException::class);

        $jwtDriver = $this->createAuth0JwtDriver();

        (new JwtTokenEncoder($jwtDriver))->encode(new JwtEasyApiToken(['scopes' => 1], ''));
    }

    private function createJwtTokenDecoder(): JwtTokenDecoder
    {
        return new JwtTokenDecoder($this->createJwtEasyApiTokenFactory($this->createAuth0JwtDriver()));
    }
}
