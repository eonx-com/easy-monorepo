<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Tests\Implementations\Stubs;

use StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface;

final class IdentityUserStub implements IdentityUserInterface
{
    /**
     * @var mixed
     */
    private $identity = [];

    /**
     * @var string[]
     */
    private $userIds = [];

    /**
     * Get identity user array representation.
     *
     * @param string $service
     *
     * @return mixed[]
     */
    public function getIdentityToArray(string $service): array
    {
        return $this->identity[$service] ?? [];
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
        return $this->userIds[$service] ?? null;
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
        return $this->identity[$service][$key] ?? $default;
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
        $this->userIds[$service] = $id;
    }

    /**
     * Set identity value.
     *
     * @param string $service
     * @param string $key
     * @param mixed $value
     *
     * @return \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface
     */
    public function setIdentityValue(string $service, string $key, $value): IdentityUserInterface
    {
        $this->identity[$service][$key] = $value;

        return $this;
    }
}
