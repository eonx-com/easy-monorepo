<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface UserInterface
{
    /**
     * @return null|int|string
     * @deprecated Will be removed in 5.0.0
     *
     */
    public function getUniqueId();

    public function getUserIdentifier(): string;
}
