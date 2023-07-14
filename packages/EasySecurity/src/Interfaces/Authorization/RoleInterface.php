<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Authorization;

use Stringable;

interface RoleInterface extends Stringable
{
    public function addMetadata(string $name, mixed $value): self;

    public function getIdentifier(): string;

    public function getMetadata(?string $name = null, mixed $default = null): mixed;

    public function getName(): ?string;

    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    public function getPermissions(): array;

    public function hasMetadata(string $name): bool;

    public function removeMetadata(string $name): self;

    /**
     * @param mixed[] $metadata
     */
    public function setMetadata(array $metadata): self;
}
