<?php
declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\PermissionInterface;

final class Permission implements PermissionInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * Permission constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Get string representation of permission.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->identifier;
    }

    /**
     * Get identifier.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
