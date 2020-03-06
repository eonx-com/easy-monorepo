<?php
declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\PermissionInterface;
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
     * @param string[]|\EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     * @param null|mixed[] $metadata
     */
    public function __construct(string $identifier, array $permissions, ?string $name = null, ?array $metadata = null)
    {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->metadata = $metadata ?? [];

        $this->setPermissions($permissions);
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
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param mixed[] $permissions
     */
    private function setPermissions(array $permissions): void
    {
        // Accept only either permission instances or string to be converted
        $filterPermissions = static function ($permission): bool {
            return $permission instanceof PermissionInterface || \is_string($permission);
        };

        // Convert string to permissions
        $mapPermissions = static function ($permission): PermissionInterface {
            return $permission instanceof PermissionInterface ? $permission : new Permission($permission);
        };

        $this->permissions = \array_map($mapPermissions, \array_filter($permissions, $filterPermissions));
    }
}
