<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Encoders;

use LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException;
use LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenEncoderInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface;

final class ApiKeyAsBasicAuthUsernameEncoder implements EasyApiTokenEncoderInterface
{
    /**
     * Return encoded string representation of given API token.
     *
     * @param \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface $apiToken
     *
     * @return string
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException If given apiToken not supported
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException If encoding fails
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

        /** @var \LoyaltyCorp\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface $apiToken */
        $apiKey = $apiToken->getApiKey();

        if (empty($apiKey) === false) {
            return \base64_encode($apiKey);
        }

        throw new UnableToEncodeEasyApiTokenException(\sprintf('In "%s", api key empty.', \get_class($this)));
    }
}

\class_alias(
    ApiKeyAsBasicAuthUsernameEncoder::class,
    'StepTheFkUp\EasyApiToken\Encoders\ApiKeyAsBasicAuthUsernameEncoder',
    false
);
