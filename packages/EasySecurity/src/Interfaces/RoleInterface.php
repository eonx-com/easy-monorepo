<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface RoleInterface
{
    /**
     * Get string representation of role.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Get identifier.
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Get metadata.
     *
     * @return mixed[]
     */
    public function getMetadata(): array;

    /**
     * Get name.
     *
     * @return null|string
     */
    public function getName(): ?string;

    /**
     * Get permissions.
     *
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissions(): array;
}
