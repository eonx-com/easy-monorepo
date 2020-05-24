<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Authorization;

use EonX\EasySecurity\Authorization\Helpers\AuthorizationMatrixFormatter;
use EonX\EasySecurity\Interfaces\Authorization\RoleInterface;

/**
 * Not final on purpose for BC compatibility until 3.0.
 */
class Role implements RoleInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var mixed[]
     */
    private $metadata;

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    private $permissions;

    /**
     * @param null|string[]|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[] $permissions
     * @param null|mixed[] $metadata
     */
    public function __construct(
        string $identifier,
        ?array $permissions = null,
        ?string $name = null,
        ?array $metadata = null
    ) {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->metadata = $metadata ?? [];
        $this->permissions = AuthorizationMatrixFormatter::formatPermissions($permissions ?? []);
    }

    public function __toString(): string
    {
        return $this->identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return mixed[]
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
