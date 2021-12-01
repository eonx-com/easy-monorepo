<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External;

use EonX\EasyApiToken\Exceptions\MethodNotSupportedException;
use EonX\EasyApiToken\External\AwsCognito\Interfaces\JwkFetcherInterface;
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
     * @param null|string[] $allowedAlgos
     */
    public function __construct(JwkFetcherInterface $jwkFetcher, ?array $allowedAlgos = null, ?int $leeway = null)
    {
        $this->jwkFetcher = $jwkFetcher;
        $this->allowedAlgos = $allowedAlgos ?? [];
        $this->leeway = $leeway;
    }

    public function decode(string $token)
    {
        if ($this->leeway !== null) {
            JWT::$leeway = $this->leeway;
        }

        return JWT::decode($token, $this->jwkFetcher->getJwks(), $this->allowedAlgos);
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
