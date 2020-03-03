<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

interface EasyApiTokenInterface
{
    /**
     * Get original string token.
     *
     * @return string
     */
    public function getOriginalToken(): string;

    /**
     * Get token payload.
     *
     * @return mixed[]
     */
    public function getPayload(): array;
}
