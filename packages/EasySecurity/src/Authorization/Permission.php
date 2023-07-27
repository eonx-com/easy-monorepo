<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization;

use EonX\EasySecurity\Interfaces\Authorization\PermissionInterface;

final class Permission implements PermissionInterface
{
    public function __construct(
        private string $identifier,
    ) {
    }

    public function __toString(): string
    {
        return $this->identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
