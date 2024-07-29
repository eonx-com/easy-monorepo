<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\Factory;

use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CachedAuthorizationMatrixFactory implements AuthorizationMatrixFactoryInterface
{
    public const CACHE_KEY = 'easy_security.authorization_matrix_key';

    private readonly string $key;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly AuthorizationMatrixFactoryInterface $decorated,
        ?string $key = null,
    ) {
        $this->key = $key ?? self::CACHE_KEY;
    }

    public function create(): AuthorizationMatrixProviderInterface
    {
        return $this->cache->get(
            $this->key,
            fn (ItemInterface $item): AuthorizationMatrixProviderInterface => $this->decorated->create()
        );
    }

    public function getDecorated(): AuthorizationMatrixFactoryInterface
    {
        return $this->decorated;
    }
}
