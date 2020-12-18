<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Authorization;

interface RoleInterface
{
    public function __toString(): string;

    public function getIdentifier(): string;

    /**
     * @return mixed[]
     */
    public function getMetadata(): array;

    public function getName(): ?string;

    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    public function getPermissions(): array;
}
