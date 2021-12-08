<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External;

use EonX\EasyApiToken\Exceptions\InvalidJwtException;
use EonX\EasyApiToken\Exceptions\MethodNotSupportedException;
use EonX\EasyApiToken\External\AwsCognito\Interfaces\JwkFetcherInterface;
use EonX\EasyApiToken\External\AwsCognito\Interfaces\UserPoolConfigInterface;
use EonX\EasyApiToken\External\AwsCognito\JwkFetcher;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use Firebase\JWT\JWT;

final class AwsCognitoJwtDriver implements JwtDriverInterface
{
    /**
     * @var string[]
     */
    private $allowedAlgos;

    /**
     * @var \EonX\EasyApiToken\External\AwsCognito\Interfaces\JwkFetcherInterface
     */
    private $jwkFetcher;

    /**
     * @var null|int
     */
    private $leeway;

    /**
     * @var \EonX\EasyApiToken\External\AwsCognito\Interfaces\UserPoolConfigInterface
     */
    private $userPoolConfig;

    /**
     * @param null|string[] $allowedAlgos
     */
    public function __construct(
        UserPoolConfigInterface $userPoolConfig,
        ?JwkFetcherInterface $jwkFetcher = null,
        ?array $allowedAlgos = null,
        ?int $leeway = null
    ) {
        $this->userPoolConfig = $userPoolConfig;
        $this->jwkFetcher = $jwkFetcher ?? new JwkFetcher();
        $this->allowedAlgos = $allowedAlgos ?? [];
        $this->leeway = $leeway;
    }

    /**
     * @throws \EonX\EasyApiToken\Exceptions\InvalidJwtException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function decode(string $token): object
    {
        if ($this->leeway !== null) {
            JWT::$leeway = $this->leeway;
        }

        $decodedToken = JWT::decode($token, $this->jwkFetcher->getJwks($this->userPoolConfig), $this->allowedAlgos);

        // Validate audience
        $audience = $decodedToken->aud ?? null;
        if ($audience !== $this->userPoolConfig->getAppClientId()) {
            throw new InvalidJwtException(\sprintf(
                'Invalid audience "%s", expected "%s"',
                $audience,
                $this->userPoolConfig->getAppClientId()
            ));
        }

        // Validate issuer
        $issuer = $decodedToken->iss ?? null;
        if ($issuer !== $this->userPoolConfig->getIssuingUrl()) {
            throw new InvalidJwtException(\sprintf(
                'Invalid issuer "%s", expected "%s"',
                $issuer,
                $this->userPoolConfig->getIssuingUrl()
            ));
        }

        return $decodedToken;
    }

    /**
     * @param mixed $input
     *
     * @throws \EonX\EasyApiToken\Exceptions\MethodNotSupportedException
     */
    public function encode($input): string
    {
        throw new MethodNotSupportedException(\sprintf('%s not supported', __FUNCTION__));
    }
}
