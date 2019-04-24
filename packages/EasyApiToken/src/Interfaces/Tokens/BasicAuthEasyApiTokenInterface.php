<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces\Tokens;

use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface;

interface BasicAuthEasyApiTokenInterface extends EasyApiTokenInterface
{
    /**
     * Get password from payload.
     *
     * @return string
     */
    public function getPassword(): string;

    /**
     * Get username from payload.
     *
     * @return string
     */
    public function getUsername(): string;
}

\class_alias(
    BasicAuthEasyApiTokenInterface::class,
    'StepTheFkUp\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface',
    false
);
