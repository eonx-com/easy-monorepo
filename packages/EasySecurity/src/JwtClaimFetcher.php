<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Interfaces\Tokens\JwtInterface;
use EonX\EasySecurity\Interfaces\JwtClaimFetcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class JwtClaimFetcher implements JwtClaimFetcherInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param null|mixed[] $default
     *
     * @return mixed[]
     */
    public function getArrayClaim(JwtInterface $token, string $claim, ?array $default = null): array
    {
        return $this->doGetClaim($token, $claim, $default ?? []);
    }

    /**
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getClaim(JwtInterface $token, string $claim, $default = null)
    {
        return $this->doGetClaim($token, $claim, $default);
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    private function doGetClaim(JwtInterface $token, string $claim, $default)
    {
        try {
            return $token->getClaimForceArray($claim);
        } catch (InvalidArgumentException $exception) {
            $this->logger->info(\sprintf(
                '[%s] Exception while getting claim "%s": %s',
                static::class,
                $claim,
                $exception->getMessage()
            ));

            return $default;
        }
    }
}
