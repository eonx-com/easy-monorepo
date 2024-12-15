<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Resolver;

use EonX\EasyApiToken\Common\Exception\InvalidArgumentException;
use EonX\EasyApiToken\Common\ValueObject\Jwt;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class JwtClaimResolver implements JwtClaimResolverInterface
{
    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function getArrayClaim(Jwt $token, string $claim, ?array $default = null): array
    {
        /** @var array $result */
        $result = $this->doGetClaim($token, $claim, $default ?? []);

        return $result;
    }

    public function getClaim(Jwt $token, string $claim, mixed $default = null): mixed
    {
        return $this->doGetClaim($token, $claim, $default);
    }

    private function doGetClaim(Jwt $token, string $claim, mixed $default): mixed
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
