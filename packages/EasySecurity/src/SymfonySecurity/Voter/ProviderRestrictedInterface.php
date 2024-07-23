<?php
declare(strict_types=1);

namespace EonX\EasySecurity\SymfonySecurity\Voter;

interface ProviderRestrictedInterface
{
    public function getRestrictedProviderUniqueId(): int|string;
}
