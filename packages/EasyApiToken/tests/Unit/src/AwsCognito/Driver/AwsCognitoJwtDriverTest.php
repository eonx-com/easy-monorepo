<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\AwsCognito\Driver;

use EonX\EasyApiToken\AwsCognito\Driver\AwsCognitoJwtDriver;
use EonX\EasyApiToken\AwsCognito\ValueObject\UserPoolConfig;
use EonX\EasyApiToken\Common\Exception\MethodNotSupportedException;
use EonX\EasyApiToken\Tests\Stub\Provider\AwsCognitoJwkProviderStub;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

final class AwsCognitoJwtDriverTest extends AbstractUnitTestCase
{
    private const string KEY_ID = 'key-id';

    protected function tearDown(): void
    {
        JWT::$leeway = 0;

        parent::tearDown();
    }

    public function testDecodeRestoresGlobalLeewayAfterFailure(): void
    {
        $userPoolConfig = new UserPoolConfig('app', 'region', 'user-pool-id');
        $jwkProvider = new AwsCognitoJwkProviderStub([self::KEY_ID => $this->getPublicKey()]);
        $sut = new AwsCognitoJwtDriver($userPoolConfig, $jwkProvider, 60);

        try {
            $sut->decode($this->createExpiredToken($userPoolConfig));
            self::fail('The token expired 120 seconds ago is decoded with a leeway of 60 seconds');
        } catch (ExpiredException) {
            self::assertSame(0, JWT::$leeway);
        }
    }

    public function testDecodeSucceedsWithLeewayWithoutChangingOtherDrivers(): void
    {
        $userPoolConfig = new UserPoolConfig('app', 'region', 'user-pool-id');
        $jwkProvider = new AwsCognitoJwkProviderStub([self::KEY_ID => $this->getPublicKey()]);
        $token = $this->createExpiredToken($userPoolConfig);

        $sut = new AwsCognitoJwtDriver($userPoolConfig, $jwkProvider, 300);

        $result = $sut->decode($token);

        self::assertSame('subject', $result->sub ?? null);
        self::assertSame(0, JWT::$leeway);
        $this->expectException(ExpiredException::class);
        $driverWithoutLeeway = new AwsCognitoJwtDriver($userPoolConfig, $jwkProvider);
        $driverWithoutLeeway->decode($token);
    }

    public function testDecodeSucceedsWithoutLeewayUsingGlobalLeeway(): void
    {
        JWT::$leeway = 300;
        $userPoolConfig = new UserPoolConfig('app', 'region', 'user-pool-id');
        $jwkProvider = new AwsCognitoJwkProviderStub([self::KEY_ID => $this->getPublicKey()]);
        $sut = new AwsCognitoJwtDriver($userPoolConfig, $jwkProvider);

        $result = $sut->decode($this->createExpiredToken($userPoolConfig));

        self::assertSame('subject', $result->sub ?? null);
        self::assertSame(300, JWT::$leeway);
    }

    public function testEncodeNotSupported(): void
    {
        $this->expectException(MethodNotSupportedException::class);

        new AwsCognitoJwtDriver(new UserPoolConfig('app', 'region', 'user-pool-id'))
            ->encode([]);
    }

    private function createExpiredToken(UserPoolConfig $userPoolConfig): string
    {
        $payload = [
            'client_id' => $userPoolConfig->getAppClientId(),
            'exp' => \time() - 120,
            'iss' => $userPoolConfig->getIssuingUrl(),
            'sub' => 'subject',
            'token_use' => 'access',
        ];

        return JWT::encode($payload, $this->getPrivateKey(), 'RS256', self::KEY_ID);
    }

    private function getPrivateKey(): string
    {
        return (string)\file_get_contents(__DIR__ . '/../../../../Fixture/keys/jwt_private.pem');
    }

    private function getPublicKey(): string
    {
        return (string)\file_get_contents(__DIR__ . '/../../../../Fixture/keys/jwt_public.pem');
    }
}
