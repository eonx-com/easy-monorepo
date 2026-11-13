<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Firebase\Driver;

use EonX\EasyApiToken\Firebase\Driver\FirebaseJwtDriver;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

final class FirebaseJwtDriverTest extends AbstractUnitTestCase
{
    private const string KEY = '1234567890123456789012345678901234567890123456789012345678901234';

    protected function tearDown(): void
    {
        JWT::$leeway = 0;

        parent::tearDown();
    }

    public function testDecodeRestoresGlobalLeewayAfterFailure(): void
    {
        $sut = new FirebaseJwtDriver('HS256', 'another-key-another-key-another-key-another-key', self::KEY, 300);

        try {
            $sut->decode($this->createExpiredToken());
            self::fail('The token signed with another key is decoded');
        } catch (SignatureInvalidException) {
            self::assertSame(0, JWT::$leeway);
        }
    }

    public function testDecodeSucceedsWithLeewayWithoutChangingOtherDrivers(): void
    {
        $token = $this->createExpiredToken();

        $result = new FirebaseJwtDriver('HS256', self::KEY, self::KEY, 300)->decode($token);

        self::assertSame('subject', $result->sub ?? null);
        self::assertSame(0, JWT::$leeway);
        $this->expectException(ExpiredException::class);
        new FirebaseJwtDriver('HS256', self::KEY, self::KEY)->decode($token);
    }

    public function testDecodeSucceedsWithoutLeewayUsingGlobalLeeway(): void
    {
        JWT::$leeway = 300;

        $result = new FirebaseJwtDriver('HS256', self::KEY, self::KEY)->decode($this->createExpiredToken());

        self::assertSame('subject', $result->sub ?? null);
        self::assertSame(300, JWT::$leeway);
    }

    private function createExpiredToken(): string
    {
        return JWT::encode(['exp' => \time() - 120, 'sub' => 'subject'], self::KEY, 'HS256');
    }
}
