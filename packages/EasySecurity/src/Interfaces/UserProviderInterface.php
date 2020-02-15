<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface UserProviderInterface
{
    /**
     * Get user for given uniqueId and data.
     *
     * @param int|string $uniqueId
     * @param mixed[] $data
     *
     * @return null|\EonX\EasySecurity\Interfaces\UserInterface
     */
    public function getUser($uniqueId, array $data): ?UserInterface;
}
