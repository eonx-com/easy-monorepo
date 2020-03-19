<?php

declare(strict_types=1);

namespace EonX\EasyIdentity\Interfaces;

interface IdentityUserIdHolderInterface
{
    public function getIdentityUserId(): string;

    public function setIdentityUserId(string $userId): void;
}
