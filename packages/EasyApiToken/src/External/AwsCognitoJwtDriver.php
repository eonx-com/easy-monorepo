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
    private const TOKEN_TYPE_ACCESS = 'access';

    private const TOKEN_TYPE_ID = 'id';

    /**
     * @var string[]
     */
    private array $allowedAlgos;

    private JwkFetcherInterface $jwkFetcher;

    /**
     * @param null|string[] $allowedAlgos
     */
    public function __construct(
        private UserPoolConfigInterface $userPoolConfig,
        ?JwkFetcherInterface $jwkFetcher = null,
        ?array $allowedAlgos = null,
        private ?int $leeway = null
    ) {
        $this->jwkFetcher = $jwkFetcher ?? new JwkFetcher();
        $this->allowedAlgos = $allowedAlgos ?? [];
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

        /** @var \stdClass $decodedToken */
        $decodedToken = JWT::decode($token, $this->jwkFetcher->getJwks($this->userPoolConfig), $this->allowedAlgos);
        $tokenType = $decodedToken->token_use ?? null;

        // Validate audience
        $audience = match ($tokenType) {
            self::TOKEN_TYPE_ACCESS => $decodedToken->client_id ?? null,
            self::TOKEN_TYPE_ID => $decodedToken->aud ?? null,
            default => null
        };
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
     * @throws \EonX\EasyApiToken\Exceptions\MethodNotSupportedException
     */
    public function encode(array|object $input): string
    {
        throw new MethodNotSupportedException(\sprintf('%s not supported', __FUNCTION__));
    }
}
