<?php

declare(strict_types=1);

namespace EonX\EasyIdentity\Implementations;

use EonX\EasyIdentity\Interfaces\IdentityUserInterface;
use EonX\EasyIdentity\Interfaces\IdentityUserServiceInterface;

final class IdentityUserService implements IdentityUserServiceInterface
{
    /**
     * @var mixed[]
     */
    private $identityIds = [];

    /**
     * @var mixed[]
     */
    private $identityValues = [];

    public function getIdentityToArray(IdentityUserInterface $user, string $service): array
    {
        return $this->identityValues[$service] ?? [];
    }

    public function getIdentityUserId(IdentityUserInterface $user, string $service)
    {
        return $this->identityIds[$service] ?? null;
    }

    /**
     * @param null|mixed $default
     *
     * @return null|mixed
     */
    public function getIdentityValue(IdentityUserInterface $user, string $service, string $key, $default = null)
    {
        return $this->identityValues[$service][$key] ?? $default;
    }

    /**
     * @param mixed $id
     */
    public function setIdentityUserId(IdentityUserInterface $user, string $service, $id): void
    {
        $this->identityIds[$service] = $id;
    }

    /**
     * @param mixed $value
     */
    public function setIdentityValue(IdentityUserInterface $user, string $service, string $key, $value): void
    {
        $this->identityValues[$service][$key] = $value;
    }
}
