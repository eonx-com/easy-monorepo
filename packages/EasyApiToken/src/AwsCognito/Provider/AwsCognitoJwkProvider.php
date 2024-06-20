<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\AwsCognito\Provider;

use EonX\EasyApiToken\AwsCognito\ValueObject\UserPoolConfigInterface;
use Firebase\JWT\JWT;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

final class AwsCognitoJwkProvider implements AwsCognitoJwkProviderInterface
{
    private const DEFAULT_CACHE_EXPIRY = 3600;

    private HttpClientInterface $httpClient;

    public function __construct(
        private readonly CacheInterface $cache = new ArrayAdapter(),
        private readonly int $cacheExpiry = self::DEFAULT_CACHE_EXPIRY,
        ?HttpClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getJwks(UserPoolConfigInterface $userPoolConfig): array
    {
        return $this->cache->get(
            \md5($userPoolConfig->getJwksUrl()),
            function (ItemInterface $item) use ($userPoolConfig): array {
                $item->expiresAfter($this->cacheExpiry);

                return $this->fetchKeysFromAws($userPoolConfig);
            }
        );
    }

    private function convertJwkToPem(array $jwk): string
    {
        $exponent = $jwk['e'] ?? null;

        if (\is_string($exponent) === false) {
            throw new UnexpectedValueException('The exponent value has to be a string.');
        }

        return (string)PublicKeyLoader::load([
            'e' => new BigInteger((string)\base64_decode($exponent, true), 256),
            'n' => new BigInteger(JWT::urlsafeB64Decode($jwk['n']), 256),
        ]);
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function fetchKeysFromAws(UserPoolConfigInterface $userPoolConfig): array
    {
        $response = $this->httpClient
            ->request('GET', $userPoolConfig->getJwksUrl())
            ->toArray();

        $keys = [];

        foreach ($response['keys'] as $jwk) {
            $keys[$jwk['kid']] = $this->convertJwkToPem($jwk);
        }

        return $keys;
    }
}
