<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Tokens\Factories;

use Exception;
use StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException;
use StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface;
use StepTheFkUp\ApiToken\Interfaces\Tokens\Factories\JwtApiTokenFactoryInterface;
use StepTheFkUp\ApiToken\Interfaces\Tokens\JwtApiTokenInterface;
use StepTheFkUp\ApiToken\Tokens\JwtApiToken;

final class JwtApiTokenFactory implements JwtApiTokenFactoryInterface
{
    /**
     * @var \StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface
     */
    private $jwtDriver;

    /**
     * JwtApiTokenFactory constructor.
     *
     * @param \StepTheFkUp\ApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
     */
    public function __construct(JwtDriverInterface $jwtDriver)
    {
        $this->jwtDriver = $jwtDriver;
    }

    /**
     * Create JwtApiToken from given string.
     *
     * @param string $token
     *
     * @return \StepTheFkUp\ApiToken\Interfaces\Tokens\JwtApiTokenInterface
     *
     * @throws \StepTheFkUp\ApiToken\Exceptions\InvalidApiTokenFromRequestException
     */
    public function createFromString(string $token): JwtApiTokenInterface
    {
        try {
            return new JwtApiToken((array)$this->jwtDriver->decode(\trim($token)));
        } catch (Exception $exception) {
            throw new InvalidApiTokenFromRequestException(
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
