<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External\AwsCognito;

use EonX\EasyApiToken\External\AwsCognito\Interfaces\JwkFetcherInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class JwkFetcher implements JwkFetcherInterface
{
    /**
     * @var int
     */
    private const CACHE_EXPIRY = 3600;

    /**
     * @var string
     */
    private const JWKS_URL_PATTERN = 'https://cognito-idp.%s.amazonaws.com/%s/.well-known/jwks.json';

    /**
     * @var \Symfony\Contracts\Cache\CacheInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $cacheExpiry;

    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $jwksUrl;

    public function __construct(
        string $region,
        string $userPoolId,
        ?CacheInterface $cache = null,
        ?int $cacheExpiry = null,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->jwksUrl = \sprintf(self::JWKS_URL_PATTERN, $region, $userPoolId);
        $this->cache = $cache ?? new ArrayAdapter();
        $this->cacheExpiry = $cacheExpiry ?? self::CACHE_EXPIRY;
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    /**
     * @return mixed[]
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getJwks(): array
    {
        return $this->cache->get(\md5($this->jwksUrl), function (ItemInterface $item): array {
            $item->expiresAfter($this->cacheExpiry);

            return $this->fetchKeysFromAws();
        });
    }

    /**
     * @return mixed[]
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function fetchKeysFromAws(): array
    {
        $response = $this->httpClient
            ->request('GET', $this->jwksUrl)
            ->toArray();

        $jwks = [];

        foreach ($response['keys'] as $key) {
            $jwks[$key['kid']] = $key['n'];
        }

        return $jwks;
    }
}
