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
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->identifier;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
