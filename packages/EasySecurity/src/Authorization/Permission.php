<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Authorization;

use EonX\EasySecurity\Interfaces\Authorization\PermissionInterface;

/**
 * Not final on purpose for BC compatibility until 3.0.
 */
class Permission implements PermissionInterface
{
    /**
     * @var string
     */
    private $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
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
