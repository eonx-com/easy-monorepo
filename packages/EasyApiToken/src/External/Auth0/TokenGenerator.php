<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External\Auth0;

use Auth0\SDK\API\Helpers\TokenGenerator as BaseTokenGenerator;
use EonX\EasyApiToken\External\Auth0\Interfaces\TokenGeneratorInterface;
use Firebase\JWT\JWT;

final class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * Audience for the ID token.
     *
     * @var string|null
     */
    private $audience;

    /**
     * Secret used to encode the token.
     *
     * @var string|null
     */
    private $secret;

    public function __construct(?string $audience = null, ?string $secret = null)
    {
        $this->audience = $audience;
        $this->secret = $secret;
    }

    /**
     * @param mixed[] $scopes
     * @param null|mixed[] $roles
     */
    public function generate(
        array $scopes,
        ?array $roles = null,
        ?string $subject = null,
        ?int $lifetime = null,
        ?bool $secretEncoded = null
    ): string {
        $secretEncoded = $secretEncoded ?? true;
        $lifetime = $lifetime ?? BaseTokenGenerator::DEFAULT_LIFETIME;

        $time = \time();
        $payload = [
            'iat' => $time,
            'scopes' => $scopes,
            'exp' => $time + $lifetime,
            'aud' => $this->audience,
        ];

        if ($subject !== null) {
            $payload['sub'] = $subject;
        }

        if ($roles !== null) {
            $payload = \array_merge($payload, $roles);
        }

        $payload['jti'] = \md5((string)\json_encode($payload));

        $secret = $secretEncoded === true ? \base64_decode(\strtr((string)$this->secret, '-_', '+/'), true) : $this->secret;

        return JWT::encode($payload, (string)$secret);
    }
}
