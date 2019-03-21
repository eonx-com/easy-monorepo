<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Interfaces;

interface IdentityUserInterface
{
    /**
     * Get identity user array representation.
     *
     * @param string $service
     *
     * @return mixed[]
     */
    public function getIdentityToArray(string $service): array;

    /**
     * Get identity user id for given service.
     *
     * @param string $service
     *
     * @return mixed
     */
    public function getIdentityUserId(string $service);

    /**
     * Get identity value.
     *
     * @param string $service
     * @param string $key
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getIdentityValue(string $service, string $key, $default = null);

    /**
     * Set identity user id for given service.
     *
     * @param string $service
     * @param mixed $id
     *
     * @return void
     */
    public function setIdentityUserId(string $service, $id): void;

    /**
     * Set identity value.
     *
     * @param string $service
     * @param string $key
     * @param mixed $value
     *
     * @return \StepTheFkUp\EasyIdentity\Interfaces\IdentityUserInterface
     */
    public function setIdentityValue(string $service, string $key, $value): self;
}
