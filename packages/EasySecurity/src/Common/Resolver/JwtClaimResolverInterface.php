<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Resolver;

use EonX\EasyApiToken\Common\ValueObject\JwtInterface;

interface JwtClaimResolverInterface
{
    public function getArrayClaim(JwtInterface $token, string $claim, ?array $default = null): array;

    public function getClaim(JwtInterface $token, string $claim, mixed $default = null): mixed;
}
