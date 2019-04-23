<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Tests\Implementations\Stubs;

use LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface;

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
     * @return void
     */
    public function setIdentityValue(string $service, string $key, $value): void
    {
        $this->identity[$service][$key] = $value;
    }
}

\class_alias(
    IdentityUserStub::class,
    'StepTheFkUp\EasyIdentity\Tests\Implementations\Stubs\IdentityUserStub',
    false
);
