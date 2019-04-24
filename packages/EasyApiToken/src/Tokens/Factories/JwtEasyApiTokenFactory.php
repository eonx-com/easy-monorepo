<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tokens\Factories;

use Exception;
use LoyaltyCorp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException;
use LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface;
use LoyaltyCorp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use LoyaltyCorp\EasyApiToken\Tokens\JwtEasyApiToken;

final class JwtEasyApiTokenFactory implements JwtEasyApiTokenFactoryInterface
{
    /**
     * @var \LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface
     */
    private $jwtDriver;

    /**
     * JwtEasyApiTokenFactory constructor.
     *
     * @param \LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     */
    public function __construct(JwtDriverInterface $jwtDriver)
    {
        $this->jwtDriver = $jwtDriver;
    }

    /**
     * Create JwtEasyApiToken from given string.
     *
     * @param string $token
     *
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
     */
    public function createFromString(string $token): JwtEasyApiTokenInterface
    {
        try {
            return new JwtEasyApiToken((array)$this->jwtDriver->decode(\trim($token)));
        } catch (Exception $exception) {
            throw new InvalidEasyApiTokenFromRequestException(
                \sprintf(
                    'Decoder "%s" unable to decode token. Message: %s',
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
    JwtEasyApiTokenFactory::class,
    'StepTheFkUp\EasyApiToken\Tokens\Factories\JwtEasyApiTokenFactory',
    false
);
