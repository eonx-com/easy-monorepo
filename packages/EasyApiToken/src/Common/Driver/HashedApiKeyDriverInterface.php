<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Driver;

use EonX\EasyApiToken\Common\ValueObject\HashedApiKey;

interface HashedApiKeyDriverInterface
{
    public function decode(string $hashedApiKey): ?HashedApiKey;

    public function encode(int|string $id, string $secret, ?string $version = null): string;
}
