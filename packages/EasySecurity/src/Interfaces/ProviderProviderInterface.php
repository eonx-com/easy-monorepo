<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface ProviderProviderInterface
{
    /**
     * @param int|string $uniqueId
     */
    public function getProvider($uniqueId): ?ProviderInterface;
}
