<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\AwsCognito\Provider;

use EonX\EasyApiToken\AwsCognito\Provider\AwsCognitoJwkProvider;
use EonX\EasyApiToken\AwsCognito\ValueObject\UserPoolConfig;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use OpenSSLAsymmetricKey;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use UnexpectedValueException;

final class AwsCognitoJwkProviderTest extends AbstractUnitTestCase
{
    private const KEYS_DIR = __DIR__ . '/../../../../Fixture/keys';

    private const KEY_ID = 'key-id';

    public function testGetJwksCachesKeys(): void
    {
        $httpClient = new MockHttpClient(new JsonMockResponse(['keys' => [self::createJwk()]]));
        $provider = new AwsCognitoJwkProvider(new ArrayAdapter(), 3600, $httpClient);
        $userPoolConfig = self::createUserPoolConfig();

        $provider->getJwks($userPoolConfig);
        $provider->getJwks($userPoolConfig);

        self::assertSame(1, $httpClient->getRequestsCount());
    }

    public function testGetJwksConvertsJwkToPem(): void
    {
        $httpClient = new MockHttpClient(new JsonMockResponse(['keys' => [self::createJwk()]]));
        $provider = new AwsCognitoJwkProvider(new ArrayAdapter(), 3600, $httpClient);
        $userPoolConfig = self::createUserPoolConfig();

        $result = $provider->getJwks($userPoolConfig);

        self::assertSame([self::KEY_ID], \array_keys($result));
        self::assertSame(self::normalizePem(self::getFixturePublicKey()), self::normalizePem($result[self::KEY_ID]));
    }

    public function testGetJwksReturnsKeyAbleToVerifySignedJwt(): void
    {
        $httpClient = new MockHttpClient(new JsonMockResponse(['keys' => [self::createJwk()]]));
        $provider = new AwsCognitoJwkProvider(new ArrayAdapter(), 3600, $httpClient);
        $token = JWT::encode(
            ['sub' => 'user-id'],
            (string)\file_get_contents(self::KEYS_DIR . '/jwt_private.pem'),
            'RS256',
            self::KEY_ID
        );

        $result = $provider->getJwks(self::createUserPoolConfig());
        $payload = JWT::decode($token, new Key($result[self::KEY_ID], 'RS256'));

        self::assertSame('user-id', $payload->sub);
    }

    public function testGetJwksThrowsExceptionWhenExponentIsNotString(): void
    {
        $jwk = self::createJwk();
        unset($jwk['e']);
        $httpClient = new MockHttpClient(new JsonMockResponse(['keys' => [$jwk]]));
        $provider = new AwsCognitoJwkProvider(new ArrayAdapter(), 3600, $httpClient);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The exponent value has to be a string.');

        $provider->getJwks(self::createUserPoolConfig());
    }

    /**
     * @return array<string, string>
     */
    private static function createJwk(): array
    {
        $publicKey = \openssl_pkey_get_public(self::getFixturePublicKey());
        self::assertInstanceOf(OpenSSLAsymmetricKey::class, $publicKey);
        $details = \openssl_pkey_get_details($publicKey);
        self::assertIsArray($details);

        return [
            'alg' => 'RS256',
            'e' => JWT::urlsafeB64Encode($details['rsa']['e']),
            'kid' => self::KEY_ID,
            'kty' => 'RSA',
            'n' => JWT::urlsafeB64Encode($details['rsa']['n']),
            'use' => 'sig',
        ];
    }

    private static function createUserPoolConfig(): UserPoolConfig
    {
        return new UserPoolConfig('app-client-id', 'ap-southeast-2', 'user-pool-id');
    }

    private static function getFixturePublicKey(): string
    {
        return (string)\file_get_contents(self::KEYS_DIR . '/jwt_public.pem');
    }

    private static function normalizePem(string $pem): string
    {
        return \trim(\str_replace("\r\n", "\n", $pem));
    }
}
