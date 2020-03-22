<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tokens\Factories;

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

    public function __construct(JwtDriverInterface $jwtDriver)
    {
        $this->jwtDriver = $jwtDriver;
    }

    public function createFromString(string $token): JwtEasyApiTokenInterface
    {
        try {
            return new JwtEasyApiToken((array)$this->jwtDriver->decode(\trim($token)), $token);
        } catch (\Throwable $exception) {
            throw new InvalidEasyApiTokenFromRequestException(
                \sprintf(
                    'Decoder "%s" unable to decode token. Message: %s',
                    static::class,
                    $exception->getMessage()
                ),
                $exception->getCode(),
                $exception
            );
        }
    }
}
