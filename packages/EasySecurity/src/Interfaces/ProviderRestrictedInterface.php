<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface ProviderRestrictedInterface
{
    public function getRestrictedProviderUniqueId(): int|string;
}
