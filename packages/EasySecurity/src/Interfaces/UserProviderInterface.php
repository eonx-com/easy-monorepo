<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface UserProviderInterface
{
    /**
     * @param int|string $uniqueId
     * @param mixed[] $data
     */
    public function getUser($uniqueId, array $data): ?UserInterface;
}
