<?php
declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\RoleInterface;

final class Role implements RoleInterface
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
     * @var \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    private $permissions;

    /**
     * Role constructor.
     *
     * @param string $identifier
     * @param \EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     * @param null|string $name
     * @param null|mixed[] $metadata
     */
    public function __construct(string $identifier, array $permissions, ?string $name = null, ?array $metadata = null)
    {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->metadata = $metadata ?? [];

        $this->permissions = \array_filter($permissions, static function ($permission): bool {
            return $permission instanceof Permission;
        });
    }

    /**
     * Get string representation of role.
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

    /**
     * Get metadata.
     *
     * @return mixed[]
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get name.
     *
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get permissions.
     *
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
