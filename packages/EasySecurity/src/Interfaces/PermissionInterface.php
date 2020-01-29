<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface PermissionInterface
{
    /**
     * Get identifier.
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Get string representation of permission.
     *
     * @return string
     */
    public function __toString(): string;
}
