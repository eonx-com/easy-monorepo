<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Interfaces;

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
    'LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenInterface',
    false
);
