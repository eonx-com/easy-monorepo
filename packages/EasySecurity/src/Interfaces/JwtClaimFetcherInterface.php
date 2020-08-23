<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use EonX\EasyApiToken\Interfaces\Tokens\JwtInterface;

interface JwtClaimFetcherInterface
{
    /**
     * @param null|mixed[] $default
     *
     * @return mixed[]
     */
    public function getArrayClaim(JwtInterface $token, string $claim, ?array $default = null): array;

    /**
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getClaim(JwtInterface $token, string $claim, $default = null);
}
