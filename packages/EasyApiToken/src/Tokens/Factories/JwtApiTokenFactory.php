<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Tokens\Factories;

use Exception;
use StepTheFkUp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException;
use StepTheFkUp\EasyApiToken\External\Interfaces\JwtDriverInterface;
use StepTheFkUp\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface;
use StepTheFkUp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use StepTheFkUp\EasyApiToken\Tokens\JwtEasyApiToken;

final class JwtEasyApiTokenFactory implements JwtEasyApiTokenFactoryInterface
{
    /**
     * @var \StepTheFkUp\EasyApiToken\External\Interfaces\JwtDriverInterface
     */
    private $jwtDriver;

    /**
     * JwtEasyApiTokenFactory constructor.
     *
     * @param \StepTheFkUp\EasyApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
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
     * @return \StepTheFkUp\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface
     *
     * @throws \StepTheFkUp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
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
    'LoyaltyCorp\EasyApiToken\Tokens\Factories\JwtEasyApiTokenFactory',
    false
);
