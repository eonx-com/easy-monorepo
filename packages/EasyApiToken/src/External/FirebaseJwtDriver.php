<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External;

use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use OpenSSLAsymmetricKey;

final class FirebaseJwtDriver implements JwtDriverInterface
{
    use FirebaseJwtVersionTrait;

    /**
     * @var string[]
     *
     * @deprecated will be removed in 5.0
     */
    private array $allowedAlgos;

    /**
     * @param null|string[] $allowedAlgos
     */
    public function __construct(
        private readonly string $algo,
        private readonly OpenSSLAsymmetricKey|string $publicKey,
        private readonly OpenSSLAsymmetricKey|string $privateKey,
        ?array $allowedAlgos = null,
        private readonly ?int $leeway = null,
    ) {
        $this->allowedAlgos = $allowedAlgos ?? [];
    }

    public function decode(string $token): object
    {
        /**
         * You can add a leeway to account for when there is a clock skew times between
         * the signing and verifying servers. It is recommended that this leeway should
         * not be bigger than a few minutes.
         *
         * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
         */
        if ($this->leeway !== null) {
            JWT::$leeway = $this->leeway;
        }

        if (self::isFirebaseJwtV6()) {
            return JWT::decode($token, new Key($this->publicKey, $this->algo));
        }

        /** @var mixed[]|string $publicKey */
        $publicKey = $this->publicKey;

        return JWT::decode($token, $publicKey, $this->allowedAlgos);
    }

    public function encode(array|object $input): string
    {
        /** @var string $privateKey */
        $privateKey = $this->privateKey;

        return JWT::encode($input, $privateKey, $this->algo);
    }
}
