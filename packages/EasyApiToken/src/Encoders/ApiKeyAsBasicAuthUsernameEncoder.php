<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Encoders;

use StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenEncoderInterface;
use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface;
use StepTheFkUp\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface;

final class ApiKeyAsBasicAuthUsernameEncoder implements EasyApiTokenEncoderInterface
{
    /**
     * Return encoded string representation of given API token.
     *
     * @param \StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface $apiToken
     *
     * @return string
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException If encoder doesn't support given apiToken
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException If encoder fails to
     * encode apiToken
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

        /** @var \StepTheFkUp\EasyApiToken\Interfaces\Tokens\ApiKeyEasyApiTokenInterface $apiToken */
        $apiKey = $apiToken->getApiKey();

        if (empty($apiKey) === false) {
            return \base64_encode($apiKey);
        }

        throw new UnableToEncodeEasyApiTokenException(\sprintf('In "%s", api key empty.', \get_class($this)));
    }
}

\class_alias(
    ApiKeyAsBasicAuthUsernameEncoder::class,
    'LoyaltyCorp\EasyApiToken\Encoders\ApiKeyAsBasicAuthUsernameEncoder',
    false
);
