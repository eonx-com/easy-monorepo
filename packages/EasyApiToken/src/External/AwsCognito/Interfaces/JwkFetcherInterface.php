<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External\AwsCognito\Interfaces;

interface JwkFetcherInterface
{
    /**
     * @return mixed[]
     */
    public function getJwks(): array;
}
