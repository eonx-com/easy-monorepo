<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Encoders;

use LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException;
use LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenEncoderInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;

final class JwtTokenEncoder implements EasyApiTokenEncoderInterface
{
    /**
     * @var \LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface
     */
    private $jwtDriver;

    /**
     * JwtTokenEncoder constructor.
     *
     * @param \LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     */
    public function __construct(JwtDriverInterface $jwtDriver)
    {
        $this->jwtDriver = $jwtDriver;
    }

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
        if (($apiToken instanceof JwtEasyApiTokenInterface) === false) {
            throw new InvalidArgumentException(\sprintf(
                'In "%s", API token expected to be instance of "%s", "%s" given.',
                \get_class($this),
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
                    \get_class($this),
                    $exception->getMessage()
                ),
                $exception->getCode(),
                $exception
            );
        }
    }
}

\class_alias(
    JwtTokenEncoder::class,
    'StepTheFkUp\EasyApiToken\Encoders\JwtTokenEncoder',
    false
);
