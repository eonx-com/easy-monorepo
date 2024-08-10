<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Resolver;

use EonX\EasyApiToken\Common\ValueObject\JwtToken;

interface JwtClaimResolverInterface
{
    public function getArrayClaim(JwtToken $jwtToken, string $claim, ?array $default = null): array;

    public function getClaim(JwtToken $jwtToken, string $claim, mixed $default = null): mixed;
}
