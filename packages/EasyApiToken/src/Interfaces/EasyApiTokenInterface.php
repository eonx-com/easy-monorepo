<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

interface EasyApiTokenInterface
{
    public function getOriginalToken(): string;

    /**
     * @return mixed[]
     */
    public function getPayload(): array;
}
