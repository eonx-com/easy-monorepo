<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

interface ApiTokenInterface
{
    public function getOriginalToken(): string;

    public function getPayload(): array;
}
