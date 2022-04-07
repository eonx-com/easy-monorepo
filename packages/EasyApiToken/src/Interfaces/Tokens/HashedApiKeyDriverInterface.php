<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Tokens;

use EonX\EasyApiToken\Tokens\HashedApiKey;

interface HashedApiKeyDriverInterface
{
    public function decode(string $hashedApiKey): ?HashedApiKey;

    public function encode(int|string $id, string $secret, ?string $version = null): string;
}
