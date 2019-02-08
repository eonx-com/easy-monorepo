<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tests\Tokens;

use StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\ApiToken\Tests\AbstractTestCase;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;

final class JwtApiTokenTest extends AbstractTestCase
{
    /**
     * JwtApiToken should return identical payload as input.
     *
     * @return void
     */
    public function getPayloadSuccessfully(): void
    {
        $payload = ['claim' => 'claim'];

        self::assertEquals($payload, (new JwtApiToken($payload))->getPayload());
    }

    /**
     * JwtApiToken should return expected claim value.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     */
    public function testGetClaimSuccessfully(): void
    {
        self::assertEquals('claim', (new JwtApiToken(['claim' => 'claim']))->getClaim('claim'));
    }

    /**
     * JwtApiToken should check successfully if claim exists.
     *
     * @return void
     */
    public function testHasClaimSuccessfully(): void
    {
        $token = new JwtApiToken(['claim' => 'claim']);

        self::assertTrue($token->hasClaim('claim'));
        self::assertFalse($token->hasClaim('invalid'));
    }

    /**
     * JwtApiToken should throw an exception when getting a claim which doesn't exist.
     *
     * @return void
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidArgumentException
     */
    public function testInvalidClaimException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JwtApiToken([]))->getClaim('invalid');
    }
}
