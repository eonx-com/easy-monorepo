<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Generator;

use Firebase\JWT\JWT;

final readonly class TokenGenerator implements TokenGeneratorInterface
{
    private const string DEFAULT_ALGO = 'HS256';

    public function __construct(
        private string $audience,
        private ?string $secret = null,
        private ?string $issuer = null,
    ) {
    }

    public function generate(
        array $scopes,
        ?array $roles = null,
        ?string $subject = null,
        ?int $lifetime = null,
        ?bool $secretEncoded = null,
    ): string {
        $secretEncoded ??= true;
        $lifetime ??= 3600;

        $time = \time();
        $payload = [
            'aud' => $this->audience,
            'exp' => $time + $lifetime,
            'iat' => $time,
            'scopes' => $scopes,
        ];

        if ($subject !== null) {
            $payload['sub'] = $subject;
        }

        if ($roles !== null) {
            $payload = \array_merge($payload, $roles);
        }

        if ($this->issuer !== null) {
            $payload['iss'] = $this->issuer;
        }

        $payload['jti'] = \md5((string)\json_encode($payload));

        $secret = $secretEncoded === true ? \base64_decode(
            \strtr($this->secret ?? '', '-_', '+/'),
            true
        ) : $this->secret;

        return JWT::encode($payload, (string)$secret, self::DEFAULT_ALGO);
    }
}
