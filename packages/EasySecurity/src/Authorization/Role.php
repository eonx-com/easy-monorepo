<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization;

use EonX\EasySecurity\Authorization\Helpers\AuthorizationMatrixFormatter;
use EonX\EasySecurity\Interfaces\Authorization\RoleInterface;

final class Role implements RoleInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    private array $permissions;

    /**
     * @param string[]|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]|null $permissions
     */
    public function __construct(
        private readonly string $identifier,
        ?array $permissions = null,
        private readonly ?string $name = null,
        private ?array $metadata = null,
    ) {
        $this->permissions = AuthorizationMatrixFormatter::formatPermissions($permissions ?? []);
    }

    public function __toString(): string
    {
        return $this->identifier;
    }

    public function addMetadata(string $name, mixed $value): RoleInterface
    {
        if ($this->metadata === null) {
            $this->metadata = [];
        }

        $this->metadata[$name] = $value;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getMetadata(?string $name = null, mixed $default = null): mixed
    {
        return $name === null ? ($this->metadata ?? []) : ($this->metadata[$name] ?? $default);
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

    public function hasMetadata(string $name): bool
    {
        return \is_array($this->metadata) && isset($this->metadata[$name]);
    }

    public function removeMetadata(string $name): RoleInterface
    {
        if ($this->metadata !== null) {
            unset($this->metadata[$name]);
        }

        return $this;
    }

    public function setMetadata(array $metadata): RoleInterface
    {
        $this->metadata = $metadata;

        return $this;
    }
}
