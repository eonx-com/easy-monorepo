<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tokens\Factories;

use Exception;
use EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException;
use EonX\EasyApiToken\External\Interfaces\JwtDriverInterface;
use EonX\EasyApiToken\Interfaces\Tokens\Factories\JwtEasyApiTokenFactoryInterface;
use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasyApiToken\Tokens\JwtEasyApiToken;

final class JwtEasyApiTokenFactory implements JwtEasyApiTokenFactoryInterface
{
    /**
     * @var \EonX\EasyApiToken\External\Interfaces\JwtDriverInterface
     */
    private $jwtDriver;

    /**
     * JwtEasyApiTokenFactory constructor.
     *
     * @param \EonX\EasyApiToken\External\Interfaces\JwtDriverInterface $jwtDriver
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
     * @return \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException
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
