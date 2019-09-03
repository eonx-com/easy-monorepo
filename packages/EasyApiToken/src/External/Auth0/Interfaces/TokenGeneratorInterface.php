<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\External\Auth0\Interfaces;

interface TokenGeneratorInterface
{
    /**
     * Create the ID token.
     *
     * @param string $scope Space separated list of scopes.
     * @param string|null  $subject Information about JWT subject.
     * @param integer|null $lifetime Lifetime of the token, in seconds.
     * @param boolean|null $secretEncoded True to base64 decode the client secret.
     *
     * @return string
     */
    public function generate(
        string $scope,
        ?string $subject = null,
        ?int $lifetime = null,
        ?bool $secretEncoded = null
    ): string;
}
