<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces;

interface EasyApiTokenInterface
{
    /**
     * Get token payload.
     *
     * @return mixed[]
     */
    public function getPayload(): array;
}

\class_alias(
    EasyApiTokenInterface::class,
    'StepTheFkUp\EasyApiToken\Interfaces\EasyApiTokenInterface',
    false
);
