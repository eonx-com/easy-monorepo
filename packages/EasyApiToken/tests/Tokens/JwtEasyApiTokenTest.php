<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Tokens;

use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;

final class JwtEasyApiTokenTest extends AbstractTestCase
{
    /**
     * JwtEasyApiToken should return identical payload as input.
     *
     * @return void
     */
    public function getPayloadSuccessfully(): void
    {
        $payload = ['claim' => 'claim'];
        $token = new JwtEasyApiToken($payload, 'original');

        self::assertEquals($payload, $token->getPayload());
        self::assertEquals('original', $token->getOriginalToken());
    }

    /**
     * JwtEasyApiToken should return expected claim value.
     *
     * @return void
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function testGetClaimSuccessfully(): void
    {
        $token = new JwtEasyApiToken(['claim' => 'claim'], 'original');

        self::assertEquals('claim', $token->getClaim('claim'));
    }

    /**
     * JwtEasyApiToken should check successfully if claim exists.
     *
     * @return void
     */
    public function testHasClaimSuccessfully(): void
    {
        $token = new JwtEasyApiToken(['claim' => 'claim'], 'original');

        self::assertTrue($token->hasClaim('claim'));
        self::assertFalse($token->hasClaim('invalid'));
    }

    /**
     * JwtEasyApiToken should throw an exception when getting a claim which doesn't exist.
     *
     * @return void
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException
     */
    public function testInvalidClaimException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JwtEasyApiToken([], ''))->getClaim('invalid');
    }
}
