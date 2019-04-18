<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Encoders;

use StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenEncoderInterface;
use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface;
use StepTheFkUp\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface;

final class BasicAuthEncoder implements EasyApiTokenEncoderInterface
{
    /**
     * Return encoded string representation of given API token.
     *
     * @param \StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface $apiToken
     *
     * @return string
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException If encoder doesn't support given apiToken
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException If encoder fails to encode apiToken
     */
    public function encode(EasyApiTokenInterface $apiToken): string
    {
        if (($apiToken instanceof BasicAuthEasyApiTokenInterface) === false) {
            throw new InvalidArgumentException(\sprintf(
                'In "%s", API token expected to be instance of "%s", "%s" given.',
                \get_class($this),
                BasicAuthEasyApiTokenInterface::class,
                \get_class($apiToken)
            ));
        }

        /** @var \StepTheFkUp\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface $apiToken */
        $username = $apiToken->getUsername();
        $password = $apiToken->getPassword();

        if (empty($username) === false && empty($password) === false) {
            return \base64_encode(\sprintf('%s:%s', $username, $password));
        }

        throw new UnableToEncodeEasyApiTokenException(\sprintf(
            'In "%s", username and/or password empty. Payload: [username => "%s", password => "%s"]',
            \get_class($this),
            $username,
            $password
        ));
    }
}

\class_alias(
    BasicAuthEncoder::class,
    'LoyaltyCorp\EasyApiToken\Encoders\BasicAuthEncoder',
    false
);
