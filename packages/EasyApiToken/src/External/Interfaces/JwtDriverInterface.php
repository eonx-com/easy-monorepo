<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\External\Interfaces;

interface JwtDriverInterface
{
    /**
     * Decode JWT token.
     *
     * @param string $token
     *
     * @return mixed[]|object
     */
    public function decode(string $token);

    /**
     * Encode given input to JWT token.
     *
     * @param mixed[]|object $input
     *
     * @return string
     */
    public function encode($input): string;
}

\class_alias(
    JwtDriverInterface::class,
    'LoyaltyCorp\EasyApiToken\External\Interfaces\JwtDriverInterface',
    false
);
