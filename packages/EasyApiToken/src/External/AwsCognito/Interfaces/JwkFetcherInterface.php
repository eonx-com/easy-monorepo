<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External\AwsCognito\Interfaces;

interface JwkFetcherInterface
{
    /**
     * @return array<string,\Firebase\JWT\Key>
     */
    public function getJwks(UserPoolConfigInterface $userPoolConfig): array;
}
