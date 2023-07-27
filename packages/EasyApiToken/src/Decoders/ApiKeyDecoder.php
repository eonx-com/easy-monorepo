<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasyApiToken\Interfaces\Tokens\HashedApiKeyDriverInterface;
use EonX\EasyApiToken\Interfaces\Tokens\HashedApiKeyInterface;
use EonX\EasyApiToken\Tokens\ApiKey;
use Symfony\Component\HttpFoundation\Request;

final class ApiKeyDecoder extends AbstractApiTokenDecoder
{
    private ?HashedApiKeyDriverInterface $hashedApiKeyDriver = null;

    public function decode(Request $request): null|ApiTokenInterface|HashedApiKeyInterface
    {
        $authorization = $this->getHeaderWithoutPrefix('Authorization', 'Basic', $request);

        if ($authorization === null) {
            // If Authorization doesn't start with Basic, return null
            return null;
        }

        $authorization = \explode(':', (string)\base64_decode($authorization, true));
        $username = \trim($authorization[0] ?? '');
        $password = \trim($authorization[1] ?? '');

        if ($username === '' || $password !== '') {
            // If Authorization doesn't contain ONLY a username, return null
            return null;
        }

        $originalToken = $username;

        return $this->hashedApiKeyDriver?->decode($originalToken) ?? new ApiKey($originalToken);
    }

    public function setHashedApiKeyDriver(HashedApiKeyDriverInterface $hashedApiKeyDriver): self
    {
        $this->hashedApiKeyDriver = $hashedApiKeyDriver;

        return $this;
    }
}
