<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tokens;

use EonX\EasyApiToken\Interfaces\Tokens\HashedApiKeyDriverInterface;
use EonX\EasyApiToken\Interfaces\Tokens\HashedApiKeyInterface;
use EonX\EasyUtils\Helpers\UrlHelper;

final class HashedApiKeyDriver implements HashedApiKeyDriverInterface
{
    public function decode(string $hashedApiKey): ?HashedApiKey
    {
        $jsonDecoded = \json_decode(UrlHelper::urlSafeBase64Decode($hashedApiKey), true) ?? [];
        $isStructureValid = isset(
            $jsonDecoded[HashedApiKeyInterface::KEY_ID],
            $jsonDecoded[HashedApiKeyInterface::KEY_SECRET]
        );

        if ($isStructureValid === false) {
            return null;
        }

        return new HashedApiKey(
            $jsonDecoded[HashedApiKeyInterface::KEY_ID],
            $jsonDecoded[HashedApiKeyInterface::KEY_SECRET],
            $hashedApiKey,
            $jsonDecoded[HashedApiKeyInterface::KEY_VERSION] ?? null
        );
    }

    public function encode(int|string $id, string $secret, ?string $version = null): string
    {
        $payload = [
            HashedApiKeyInterface::KEY_ID => $id,
            HashedApiKeyInterface::KEY_SECRET => $secret,
            HashedApiKeyInterface::KEY_VERSION => $version ?? HashedApiKeyInterface::DEFAULT_VERSION,
        ];

        return UrlHelper::urlSafeBase64Encode(\json_encode($payload) ?: '');
    }
}
