<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\AwsCognito\Driver;

use EonX\EasyApiToken\AwsCognito\Provider\AwsCognitoJwkProvider;
use EonX\EasyApiToken\AwsCognito\Provider\AwsCognitoJwkProviderInterface;
use EonX\EasyApiToken\AwsCognito\ValueObject\UserPoolConfig;
use EonX\EasyApiToken\Common\Driver\JwtDriverInterface;
use EonX\EasyApiToken\Common\Exception\InvalidJwtException;
use EonX\EasyApiToken\Common\Exception\MethodNotSupportedException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final readonly class AwsCognitoJwtDriver implements JwtDriverInterface
{
    private const DEFAULT_JWK_ALGORITHM = 'RS256';

    private const TOKEN_TYPE_ACCESS = 'access';

    private const TOKEN_TYPE_ID = 'id';

    /**
     * @todo Rename $jwkFetcher to $jwkProvider in next major version
     * @todo Rename $leeway to $jwtLeeway in next major version
     * @todo Rename $defaultJwkAlgo to $defaultJwtAlgorithm in next major version
     */
    public function __construct(
        private UserPoolConfig $userPoolConfig,
        private AwsCognitoJwkProviderInterface $jwkFetcher = new AwsCognitoJwkProvider(),
        private ?int $leeway = null,
        private string $defaultJwkAlgo = self::DEFAULT_JWK_ALGORITHM,
    ) {
    }

    /**
     * @throws \EonX\EasyApiToken\Common\Exception\InvalidJwtException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function decode(string $token): object
    {
        if ($this->leeway !== null) {
            JWT::$leeway = $this->leeway;
        }

        $jwks = $this->jwkFetcher->getJwks($this->userPoolConfig);

        foreach ($jwks as $keyId => $key) {
            $jwks[$keyId] = new Key($key, $this->defaultJwkAlgo);
        }

        $decodedToken = JWT::decode($token, $jwks);
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
     * @throws \EonX\EasyApiToken\Common\Exception\MethodNotSupportedException
     */
    public function encode(array|object $input): string
    {
        throw new MethodNotSupportedException(\sprintf('%s not supported', __FUNCTION__));
    }
}
