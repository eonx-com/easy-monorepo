<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Encoders;

use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use EonX\EasyApiToken\Interfaces\EasyApiTokenEncoderInterface;
use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;

final class JwtTokenEncoder implements EasyApiTokenEncoderInterface
{
    /**
     * @var \EonX\EasyApiToken\External\Interfaces\JwtDriverInterface
     */
    private $jwtDriver;

    public function __construct(JwtDriverInterface $jwtDriver)
    {
        $this->jwtDriver = $jwtDriver;
    }

    public function encode(EasyApiTokenInterface $apiToken): string
    {
        if (($apiToken instanceof JwtEasyApiTokenInterface) === false) {
            throw new InvalidArgumentException(\sprintf(
                'In "%s", API token expected to be instance of "%s", "%s" given.',
                static::class,
                JwtEasyApiTokenInterface::class,
                \get_class($apiToken)
            ));
        }

        try {
            return $this->jwtDriver->encode($apiToken->getPayload());
        } catch (\Throwable $exception) {
            throw new UnableToEncodeEasyApiTokenException(
                \sprintf(
                    'In "%s", unable to encode token. Reason: %s',
                    static::class,
                    $exception->getMessage()
                ),
                $exception->getCode(),
                $exception
            );
        }
    }
}
