<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Resolver;

use EonX\EasyApiToken\Common\Exception\InvalidArgumentException;
use EonX\EasyApiToken\Common\ValueObject\JwtInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class JwtClaimResolver implements JwtClaimResolverInterface
{
    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function getArrayClaim(JwtInterface $token, string $claim, ?array $default = null): array
    {
        return $this->doGetClaim($token, $claim, $default ?? []);
    }

    public function getClaim(JwtInterface $token, string $claim, mixed $default = null): mixed
    {
        return $this->doGetClaim($token, $claim, $default);
    }

    private function doGetClaim(JwtInterface $token, string $claim, mixed $default): mixed
    {
        try {
            return $token->getClaimForceArray($claim);
        } catch (InvalidArgumentException $exception) {
            $this->logger->info(\sprintf(
                '[%s] Exception while getting claim "%s": %s',
                self::class,
                $claim,
                $exception->getMessage()
            ));

            return $default;
        }
    }
}
