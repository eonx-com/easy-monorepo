<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface ProviderProviderInterface
{
    /**
     * Get provider for given uniqueId.
     *
     * @param int|string $uniqueId
     *
     * @return null|\EonX\EasySecurity\Interfaces\ProviderInterface
     */
    public function getProvider($uniqueId): ?ProviderInterface;
}
