<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

interface EasyApiTokenInterface
{
    /**
     * Get token payload.
     *
     * @return mixed[]
     */
    public function getPayload(): array;
}
