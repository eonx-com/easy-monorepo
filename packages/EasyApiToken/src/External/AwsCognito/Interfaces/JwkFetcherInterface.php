<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External\AwsCognito\Interfaces;

interface JwkFetcherInterface
{
    public function getJwks(UserPoolConfigInterface $userPoolConfig): array;
}
