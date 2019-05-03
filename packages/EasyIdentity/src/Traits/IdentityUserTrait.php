<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Traits;

trait IdentityUserTrait
{
    /** @var array */
    private $identityIds = [];

    /** @var array */
    private $identityValues = [];

    /**
     * Get identity user array representation.
     *
     * @param string $service
     *
     * @return mixed[]
     */
    public function getIdentityToArray(string $service): array
    {
        return $this->identityValues[$service] ?? [];
    }

    /**
     * Get identity user id for given service.
     *
     * @param string $service
     *
     * @return mixed
     */
    public function getIdentityUserId(string $service)
    {
        return $this->identityIds[$service] ?? null;
    }

    /**
     * Get identity value.
     *
     * @param string $service
     * @param string $key
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getIdentityValue(string $service, string $key, $default = null)
    {
        return $this->identityValues[$service][$key] ?? $default;
    }

    /**
     * Set identity user id for given service.
     *
     * @param string $service
     * @param mixed $id
     *
     * @return void
     */
    public function setIdentityUserId(string $service, $id): void
    {
        $this->identityIds[$service] = $id;
    }

    /**
     * Set identity value.
     *
     * @param string $service
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setIdentityValue(string $service, string $key, $value): void
    {
        $this->identityValues[$service][$key] = $value;
    }
}
