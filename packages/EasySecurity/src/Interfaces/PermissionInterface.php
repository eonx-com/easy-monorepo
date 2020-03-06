<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface PermissionInterface
{
    public function __toString(): string;

    public function getIdentifier(): string;
}
