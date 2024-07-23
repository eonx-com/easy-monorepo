<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Firebase\Driver;

use EonX\EasyApiToken\Common\Driver\JwtDriverInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use OpenSSLAsymmetricKey;

final readonly class FirebaseJwtDriver implements JwtDriverInterface
{
    public function __construct(
        private string $algo,
        private OpenSSLAsymmetricKey|string $publicKey,
        private OpenSSLAsymmetricKey|string $privateKey,
        private ?int $leeway = null,
    ) {
    }

    public function decode(string $token): object
    {
        /**
         * You can add a leeway to account for when there is a clock skew times between
         * the signing and verifying servers. It is recommended that this leeway should
         * not be bigger than a few minutes.
         *
         * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef.
         */
        if ($this->leeway !== null) {
            JWT::$leeway = $this->leeway;
        }

        return JWT::decode($token, new Key($this->publicKey, $this->algo));
    }

    public function encode(array|object $input): string
    {
        /** @var string $privateKey */
        $privateKey = $this->privateKey;

        if (\is_object($input)) {
            $input = (array)$input;
        }

        return JWT::encode($input, $privateKey, $this->algo);
    }
}
