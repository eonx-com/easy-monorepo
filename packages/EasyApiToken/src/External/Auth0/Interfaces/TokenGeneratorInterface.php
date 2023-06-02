<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External\Auth0\Interfaces;

interface TokenGeneratorInterface
{
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
    ): string;
}
