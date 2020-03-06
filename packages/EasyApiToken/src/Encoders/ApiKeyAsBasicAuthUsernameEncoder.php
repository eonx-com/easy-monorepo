<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Encoders;

use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use EonX\EasyApiToken\Interfaces\EasyApiTokenEncoderInterface;
use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface;

final class ApiKeyAsBasicAuthUsernameEncoder implements EasyApiTokenEncoderInterface
{
    public function encode(EasyApiTokenInterface $apiToken): string
    {
        if (($apiToken instanceof ApiKeyEasyApiTokenInterface) === false) {
            throw new InvalidArgumentException(\sprintf(
                'In "%s", API token expected to be instance of "%s", "%s" given.',
                static::class,
                ApiKeyEasyApiTokenInterface::class,
                \get_class($apiToken)
            ));
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface $apiToken */
        $apiKey = $apiToken->getApiKey();

        if (empty($apiKey) === false) {
            return \base64_encode($apiKey);
        }

        throw new UnableToEncodeEasyApiTokenException(\sprintf('In "%s", api key empty.', static::class));
    }
}
