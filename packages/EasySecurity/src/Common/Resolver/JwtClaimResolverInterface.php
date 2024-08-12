<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Resolver;

use EonX\EasyApiToken\Common\ValueObject\Jwt;

interface JwtClaimResolverInterface
{
    public function getArrayClaim(Jwt $token, string $claim, ?array $default = null): array;

    public function getClaim(Jwt $token, string $claim, mixed $default = null): mixed;
}
