<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Interfaces\Tokens\HashedApiKeyInterface;
use EonX\EasyApiToken\Tokens\ApiKey;
use EonX\EasyApiToken\Tokens\HashedApiKey;
use Symfony\Component\HttpFoundation\Request;

final class ApiKeyDecoder extends AbstractApiTokenDecoder
{
    public function decode(Request $request): null|ApiTokenInterface|HashedApiKeyInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Basic', $request);

        if ($authorization === null) {
            // If Authorization doesn't start with Basic, return null
            return null;
        }

        $authorization = \explode(':', (string)\base64_decode($authorization, true));

        if (empty(\trim($authorization[0] ?? '')) === true || empty(\trim($authorization[1] ?? '')) === false) {
            // If Authorization doesn't contain ONLY a username, return null
            return null;
        }

        $originalToken = \trim($authorization[0]);

        return $this->decodeHashedApiKey($originalToken) ?? new ApiKey($originalToken);
    }

    private function decodeHashedApiKey(string $originalToken): ?HashedApiKeyInterface
    {
        $jsonDecoded = \json_decode(\base64_decode($originalToken) ?: '{}', true) ?? [];
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
            $originalToken,
            $jsonDecoded[HashedApiKeyInterface::KEY_VERSION] ?? null
        );
    }
}
