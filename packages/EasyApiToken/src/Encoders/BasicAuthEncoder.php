<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Encoders;

use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use EonX\EasyApiToken\Interfaces\EasyApiTokenEncoderInterface;
use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface;

final class BasicAuthEncoder implements EasyApiTokenEncoderInterface
{
    public function encode(EasyApiTokenInterface $apiToken): string
    {
        if (($apiToken instanceof BasicAuthEasyApiTokenInterface) === false) {
            throw new InvalidArgumentException(\sprintf(
                'In "%s", API token expected to be instance of "%s", "%s" given.',
                static::class,
                BasicAuthEasyApiTokenInterface::class,
                \get_class($apiToken)
            ));
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface $apiToken */
        $username = $apiToken->getUsername();
        $password = $apiToken->getPassword();

        if (empty($username) === false && empty($password) === false) {
            return \base64_encode(\sprintf('%s:%s', $username, $password));
        }

        throw new UnableToEncodeEasyApiTokenException(\sprintf(
            'In "%s", username and/or password empty. Payload: [username => "%s", password => "%s"]',
            static::class,
            $username,
            $password
        ));
    }
}
