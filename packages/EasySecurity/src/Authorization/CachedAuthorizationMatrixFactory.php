<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Authorization;

use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CachedAuthorizationMatrixFactory implements AuthorizationMatrixFactoryInterface
{
    public const CACHE_KEY = 'easy_security.authorization_matrix_key';

    private string $key;

    public function __construct(
        private CacheInterface $cache,
        private AuthorizationMatrixFactoryInterface $decorated,
        ?string $key = null,
    ) {
        $this->key = $key ?? self::CACHE_KEY;
    }

    public function create(): AuthorizationMatrixInterface
    {
        return $this->cache->get(
            $this->key,
            fn (ItemInterface $item): AuthorizationMatrixInterface => $this->decorated->create()
        );
    }

    public function getDecorated(): AuthorizationMatrixFactoryInterface
    {
        return $this->decorated;
    }
}
