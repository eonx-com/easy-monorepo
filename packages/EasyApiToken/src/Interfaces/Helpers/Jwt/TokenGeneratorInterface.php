<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces\Helpers\Jwt;

interface TokenGeneratorInterface
{
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
    ): string;
}
