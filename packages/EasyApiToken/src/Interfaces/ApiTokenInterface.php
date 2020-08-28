<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

interface ApiTokenInterface
{
    public function getOriginalToken(): string;

    /**
     * @return mixed[]
     */
    public function getPayload(): array;
}

\class_alias(ApiTokenInterface::class, EasyApiTokenInterface::class);
