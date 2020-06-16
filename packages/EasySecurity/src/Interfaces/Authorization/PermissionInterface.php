<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Authorization;

interface PermissionInterface extends \EonX\EasySecurity\Interfaces\PermissionInterface
{
    public function __toString(): string;

    public function getIdentifier(): string;
}
