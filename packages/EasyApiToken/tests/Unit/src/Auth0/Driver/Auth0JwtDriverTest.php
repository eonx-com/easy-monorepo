<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Auth0\Driver;

use EonX\EasyApiToken\Auth0\Driver\Auth0JwtDriver;
use EonX\EasyApiToken\Common\Exception\InvalidConfigurationException;
use EonX\EasyApiToken\Common\Exception\InvalidEasyApiTokenFromRequestException;
use EonX\EasyApiToken\Common\Generator\TokenGenerator;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class Auth0JwtDriverTest extends AbstractUnitTestCase
{
    private const string AUDIENCE = 'my-identifier';

    private const string DOMAIN = 'example.com';

    private const string KEY = '1234567890123456789012345678901234567890123456789012345678901234';

    private const string TENANT_ISSUER = 'https://example.com/';

    /**
     * @see testDecodeSucceedsWithAuthorizedIssuer
     */
    public static function provideAuthorizedIssuers(): iterable
    {
        yield 'the first authorized issuer' => [
            'authorizedIss' => ['https://auth.example.com/', 'https://second.example.com/'],
            'issuer' => 'https://auth.example.com/',
        ];

        yield 'the second authorized issuer' => [
            'authorizedIss' => ['https://auth.example.com/', 'https://second.example.com/'],
            'issuer' => 'https://second.example.com/',
        ];

        yield 'the tenant domain issuer when it is authorized' => [
            'authorizedIss' => ['https://auth.example.com/', self::TENANT_ISSUER],
            'issuer' => self::TENANT_ISSUER,
        ];
    }

    /**
     * @see testDecodeThrowsWithUnauthorizedIssuer
     */
    public static function provideUnauthorizedIssuers(): iterable
    {
        yield 'the tenant domain issuer when it is not authorized' => [
            'issuer' => self::TENANT_ISSUER,
        ];

        yield 'an unknown issuer' => [
            'issuer' => 'https://unknown.example.com/',
        ];
    }

    public function testConstructorThrowsWithoutAuthorizedIssuers(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Auth0 requires at least one authorized issuer');

        $this->createDriver([]);
    }

    #[DataProvider('provideAuthorizedIssuers')]
    public function testDecodeSucceedsWithAuthorizedIssuer(array $authorizedIss, string $issuer): void
    {
        $sut = $this->createDriver($authorizedIss);

        $result = $sut->decode($this->createToken($issuer));

        self::assertSame($issuer, $result['iss']);
    }

    #[DataProvider('provideUnauthorizedIssuers')]
    public function testDecodeThrowsWithUnauthorizedIssuer(string $issuer): void
    {
        $sut = $this->createDriver(['https://auth.example.com/']);

        $this->expectException(InvalidEasyApiTokenFromRequestException::class);
        $this->expectExceptionMessage(\sprintf('Issuer "%s" is not authorized', $issuer));

        $sut->decode($this->createToken($issuer));
    }

    /**
     * @param string[] $authorizedIss
     */
    private function createDriver(array $authorizedIss): Auth0JwtDriver
    {
        return new Auth0JwtDriver([self::AUDIENCE], $authorizedIss, self::DOMAIN, self::KEY, null, ['HS256']);
    }

    private function createToken(string $issuer): string
    {
        return new TokenGenerator(self::AUDIENCE, self::KEY, $issuer)
            ->generate([], null, null, null, false);
    }
}
