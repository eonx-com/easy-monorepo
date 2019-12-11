<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\External\Auth0\Interfaces;

interface TokenGeneratorInterface
{
    /**
     * Create the ID token.
     *
     * @param mixed[] $scopes Array of scopes to include.
     * @param mixed[][]|null $roles Array of roles this token can be used by.
     * @param string|null  $subject Information about JWT subject.
     * @param integer|null $lifetime Lifetime of the token, in seconds.
     * @param boolean|null $secretEncoded True to base64 decode the client secret.
     *
     * @return string
     */
    public function generate(
        array $scopes,
        ?array $roles = null,
        ?string $subject = null,
        ?int $lifetime = null,
        ?bool $secretEncoded = null
    ): string;
}
