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
    /**
     * Return encoded string representation of given API token.
     *
     * @param \EonX\EasyApiToken\Interfaces\EasyApiTokenInterface $apiToken
     *
     * @return string
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidArgumentException If given apiToken not supported
     * @throws \EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException If encoding fails
     */
    public function encode(EasyApiTokenInterface $apiToken): string
    {
        if (($apiToken instanceof ApiKeyEasyApiTokenInterface) === false) {
            throw new InvalidArgumentException(\sprintf(
                'In "%s", API token expected to be instance of "%s", "%s" given.',
                \get_class($this),
                ApiKeyEasyApiTokenInterface::class,
                \get_class($apiToken)
            ));
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface $apiToken */
        $apiKey = $apiToken->getApiKey();

        if (empty($apiKey) === false) {
            return \base64_encode($apiKey);
        }

        throw new UnableToEncodeEasyApiTokenException(\sprintf('In "%s", api key empty.', \get_class($this)));
    }
}
