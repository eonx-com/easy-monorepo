<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;

interface JwtClaimFetcherInterface
{
    /**
     * @param null|mixed[] $default
     *
     * @return mixed[]
     */
    public function getArrayClaim(JwtEasyApiTokenInterface $token, string $claim, ?array $default = null): array;

    /**
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getClaim(JwtEasyApiTokenInterface $token, string $claim, $default = null);
}
