<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Authorization;

use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CachedAuthorizationMatrixFactory implements AuthorizationMatrixFactoryInterface
{
    /**
     * @var string
     */
    public const CACHE_KEY = 'easy_security.authorization_matrix_key';

    /**
     * @var \Symfony\Contracts\Cache\CacheInterface
     */
    private $cache;

    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface
     */
    private $decorated;

    /**
     * @var string
     */
    private $key;

    public function __construct(
        CacheInterface $cache,
        AuthorizationMatrixFactoryInterface $decorated,
        ?string $key = null
    ) {
        $this->cache = $cache;
        $this->decorated = $decorated;
        $this->key = $key ?? self::CACHE_KEY;
    }

    public function create(): AuthorizationMatrixInterface
    {
        return $this->cache->get($this->key, function (ItemInterface $item): AuthorizationMatrixInterface {
            return $this->decorated->create();
        });
    }

    public function getDecorated(): AuthorizationMatrixFactoryInterface
    {
        return $this->decorated;
    }
}
