<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\ValueObject;

use EonX\EasyApiToken\Common\Exception\InvalidArgumentException;
use EonX\EasyApiToken\Common\ValueObject\Jwt;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;
use stdClass;

final class JwtTest extends AbstractUnitTestCase
{
    public function testGetClaimForceArraySuccessfully(): void
    {
        $claim = new stdClass();
        $token = new Jwt([
            'claim' => $claim,
        ], 'original');

        self::assertEquals($claim, $token->getClaim('claim'));
        self::assertEquals([], $token->getClaimForceArray('claim'));
    }

    public function testGetClaimForceArrayWithProperties(): void
    {
        $claim = new stdClass();
        $claim->key = 'value';

        $subClaim = new stdClass();
        $subClaim->key1 = 'value1';

        $claim->subClaim = $subClaim;

        $token = new Jwt([
            'claim' => $claim,
        ], 'original');

        $expected = [
            'key' => 'value',
            'subClaim' => [
                'key1' => 'value1',
            ],
        ];

        self::assertEquals($expected, $token->getClaimForceArray('claim'));
    }

    public function testGetClaimSuccessfully(): void
    {
        $token = new Jwt([
            'claim' => 'claim',
        ], 'original');

        self::assertSame('claim', $token->getClaim('claim'));
    }

    public function testGetPayloadSuccessfully(): void
    {
        $payload = [
            'claim' => 'claim',
        ];
        $token = new Jwt($payload, 'original');

        self::assertEquals($payload, $token->getPayload());
        self::assertSame('original', $token->getOriginalToken());
    }

    public function testHasClaimSuccessfully(): void
    {
        $token = new Jwt([
            'claim' => 'claim',
        ], 'original');

        self::assertTrue($token->hasClaim('claim'));
        self::assertFalse($token->hasClaim('invalid'));
    }

    public function testInvalidClaimException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Jwt([], '')->getClaim('invalid');
    }
}
