<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\AwsCognito\Provider;

use EonX\EasyApiToken\AwsCognito\ValueObject\UserPoolConfig;
use Firebase\JWT\JWT;
use LogicException;
use phpseclib3\Crypt\PublicKeyLoader as Phpseclib3PublicKeyLoader;
use phpseclib3\Math\BigInteger as Phpseclib3BigInteger;
use phpseclib4\Crypt\PublicKeyLoader as Phpseclib4PublicKeyLoader;
use phpseclib4\Math\BigInteger as Phpseclib4BigInteger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

final readonly class AwsCognitoJwkProvider implements AwsCognitoJwkProviderInterface
{
    private const int DEFAULT_CACHE_EXPIRY = 3600;

    private HttpClientInterface $httpClient;

    public function __construct(
        private CacheInterface $cache = new ArrayAdapter(),
        private int $cacheExpiry = self::DEFAULT_CACHE_EXPIRY,
        ?HttpClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getJwks(UserPoolConfig $userPoolConfig): array
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

        $exponent = (string)\base64_decode($exponent, true);
        $modulus = JWT::urlsafeB64Decode($jwk['n']);

        // Version 4.x of phpseclib moved the same API from the phpseclib3 namespace to phpseclib4
        if (\class_exists(Phpseclib4PublicKeyLoader::class)) {
            return (string)Phpseclib4PublicKeyLoader::load([
                'e' => new Phpseclib4BigInteger($exponent, 256),
                'n' => new Phpseclib4BigInteger($modulus, 256),
            ]);
        }

        if (\class_exists(Phpseclib3PublicKeyLoader::class)) {
            return (string)Phpseclib3PublicKeyLoader::load([
                'e' => new Phpseclib3BigInteger($exponent, 256),
                'n' => new Phpseclib3BigInteger($modulus, 256),
            ]);
        }

        throw new LogicException('The phpseclib/phpseclib package (^3.0 or ^4.0) is required to convert JWK to PEM.');
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function fetchKeysFromAws(UserPoolConfig $userPoolConfig): array
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
