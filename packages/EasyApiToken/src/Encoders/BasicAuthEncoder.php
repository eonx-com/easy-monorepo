<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Encoders;

use LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException;
use LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenEncoderInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface;

final class BasicAuthEncoder implements EasyApiTokenEncoderInterface
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
        if (($apiToken instanceof BasicAuthEasyApiTokenInterface) === false) {
            throw new InvalidArgumentException(\sprintf(
                'In "%s", API token expected to be instance of "%s", "%s" given.',
                \get_class($this),
                BasicAuthEasyApiTokenInterface::class,
                \get_class($apiToken)
            ));
        }

        /** @var \LoyaltyCorp\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface $apiToken */
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
    'StepTheFkUp\EasyApiToken\Encoders\BasicAuthEncoder',
    false
);
