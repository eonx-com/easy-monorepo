<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Encoders;

use StepTheFkUp\EasyApiToken\Exceptions\InvalidArgumentException;
use StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException;
use StepTheFkUp\EasyApiToken\External\Interfaces\JwtDriverInterface;
use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenEncoderInterface;
use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface;
use StepTheFkUp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;

final class JwtTokenEncoder implements EasyApiTokenEncoderInterface
{
    /**
     * @var \StepTheFkUp\EasyApiToken\External\Interfaces\JwtDriverInterface
     */
    private $jwtDriver;

    /**
     * JwtTokenEncoder constructor.
     *
     * @param \StepTheFkUp\EasyApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     */
    public function __construct(JwtDriverInterface $jwtDriver)
    {
        $this->jwtDriver = $jwtDriver;
    }

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
    'LoyaltyCorp\EasyApiToken\Encoders\JwtTokenEncoder',
    false
);
