<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Generator;

interface TokenGeneratorInterface
{
    public function generate(
        array $scopes,
        ?array $roles = null,
        ?string $subject = null,
        ?int $lifetime = null,
        ?bool $secretEncoded = null,
    ): string;
}
