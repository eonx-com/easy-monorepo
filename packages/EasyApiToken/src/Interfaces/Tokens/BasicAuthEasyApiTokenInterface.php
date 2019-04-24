<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Interfaces\Tokens;

use StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface;

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
    'LoyaltyCorp\EasyApiToken\Interfaces\Tokens\BasicAuthEasyApiTokenInterface',
    false
);
