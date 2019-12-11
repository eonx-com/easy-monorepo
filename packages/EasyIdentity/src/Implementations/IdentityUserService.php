<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Implementations;

use EonX\EasyIdentity\Interfaces\IdentityUserInterface;
use EonX\EasyIdentity\Interfaces\IdentityUserServiceInterface;

class IdentityUserService implements IdentityUserServiceInterface
{
    /**
     * @var array
     */
    private $identityIds = [];

    /**
     * @var array
     */
    private $identityValues = [];

    /**
     * {@inheritdoc}
     */
    public function getIdentityToArray(IdentityUserInterface $user, string $service): array
    {
        return $this->identityValues[$service] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityUserId(IdentityUserInterface $user, string $service)
    {
        return $this->identityIds[$service] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityValue(IdentityUserInterface $user, string $service, string $key, $default = null)
    {
        return $this->identityValues[$service][$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentityUserId(IdentityUserInterface $user, string $service, $id): void
    {
        $this->identityIds[$service] = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentityValue(IdentityUserInterface $user, string $service, string $key, $value): void
    {
        $this->identityValues[$service][$key] = $value;
    }

}
