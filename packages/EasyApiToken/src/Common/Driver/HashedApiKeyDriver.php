<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Driver;

use EonX\EasyApiToken\Common\ValueObject\HashedApiKey;
use EonX\EasyUtils\Common\Helper\UrlHelper;

final readonly class HashedApiKeyDriver implements HashedApiKeyDriverInterface
{
    public function decode(string $hashedApiKey): ?HashedApiKey
    {
        $jsonDecoded = \json_decode(UrlHelper::urlSafeBase64Decode($hashedApiKey), true);

        if (\is_array($jsonDecoded) === false) {
            return null;
        }

        $isStructureValid = isset(
            $jsonDecoded[HashedApiKey::KEY_ID],
            $jsonDecoded[HashedApiKey::KEY_SECRET]
        );

        if ($isStructureValid === false) {
            return null;
        }

        return new HashedApiKey(
            $jsonDecoded[HashedApiKey::KEY_ID],
            $jsonDecoded[HashedApiKey::KEY_SECRET],
            $hashedApiKey,
            $jsonDecoded[HashedApiKey::KEY_VERSION] ?? null
        );
    }

    public function encode(int|string $id, string $secret, ?string $version = null): string
    {
        $payload = [
            HashedApiKey::KEY_ID => $id,
            HashedApiKey::KEY_SECRET => $secret,
            HashedApiKey::KEY_VERSION => $version ?? HashedApiKey::DEFAULT_VERSION,
        ];

        return UrlHelper::urlSafeBase64Encode(\json_encode($payload) ?: '');
    }
}
