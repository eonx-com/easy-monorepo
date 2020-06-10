<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface ProviderRestrictedInterface
{
    /**
     * @return null|int|string
     */
    public function getRestrictedProviderUniqueId();
}
