<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Tokens;

use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;

final class JwtEasyApiTokenTest extends AbstractTestCase
{
    public function getPayloadSuccessfully(): void
    {
        $payload = ['claim' => 'claim'];
        $token = new JwtEasyApiToken($payload, 'original');

        self::assertEquals($payload, $token->getPayload());
        self::assertEquals('original', $token->getOriginalToken());
    }

    public function testGetClaimSuccessfully(): void
    {
        $token = new JwtEasyApiToken(['claim' => 'claim'], 'original');

        self::assertEquals('claim', $token->getClaim('claim'));
    }

    public function testHasClaimSuccessfully(): void
    {
        $token = new JwtEasyApiToken(['claim' => 'claim'], 'original');

        self::assertTrue($token->hasClaim('claim'));
        self::assertFalse($token->hasClaim('invalid'));
    }

    public function testInvalidClaimException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JwtEasyApiToken([], ''))->getClaim('invalid');
    }
}
