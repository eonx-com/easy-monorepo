<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\External\Auth0;

use Firebase\JWT\JWT;
use LoyaltyCorp\EasyApiToken\External\Auth0\Interfaces\TokenGeneratorInterface;
use Auth0\SDK\API\Helpers\TokenGenerator as BaseTokenGenerator;

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

    /**
     * TokenGenerator constructor.
     *
     * @param string|null $audience ID token audience to set.
     * @param string|null $secret Token encryption secret to encode the token.
     */
    public function __construct(?string $audience = null, ?string $secret = null)
    {
        $this->audience = $audience;
        $this->secret = $secret;
    }

    /**
     * Create the ID token.
     *
     * @param mixed[] $scopes Array of scopes to include.
     * @param string|null  $subject Information about JWT subject.
     * @param integer|null $lifetime Lifetime of the token, in seconds.
     * @param boolean|null $secretEncoded True to base64 decode the client secret.
     *
     * @return string
     */
    public function generate(
        array $scopes,
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
            'aud' => $this->audience
        ];

        if ($subject !== null) {
            $payload['sub'] = $subject;
        }

        $payload['jti'] = \md5(\json_encode($payload));

        $secret = $secretEncoded === true ? \base64_decode(\strtr($this->secret, '-_', '+/')) : $this->secret;

        return JWT::encode($payload, $secret);
    }
}
