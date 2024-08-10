<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Resolver;

use EonX\EasyApiToken\Common\Exception\InvalidArgumentException;
use EonX\EasyApiToken\Common\ValueObject\JwtToken;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class JwtClaimResolver implements JwtClaimResolverInterface
{
    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function getArrayClaim(JwtToken $jwtToken, string $claim, ?array $default = null): array
    {
        return $this->doGetClaim($jwtToken, $claim, $default ?? []);
    }

    public function getClaim(JwtToken $jwtToken, string $claim, mixed $default = null): mixed
    {
        return $this->doGetClaim($jwtToken, $claim, $default);
    }

    private function doGetClaim(JwtToken $jwtToken, string $claim, mixed $default): mixed
    {
        try {
            return $jwtToken->getClaimForceArray($claim);
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
