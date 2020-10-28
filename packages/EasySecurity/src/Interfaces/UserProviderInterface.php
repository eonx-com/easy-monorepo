<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

/**
 * @deprecated Since 2.4, will be removed in 3.0. Use a security context configurator instead.
 */
interface UserProviderInterface
{
    /**
     * @param int|string $uniqueId
     * @param mixed[] $data
     */
    public function getUser($uniqueId, array $data): ?UserInterface;
}
