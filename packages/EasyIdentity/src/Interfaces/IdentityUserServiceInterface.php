<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Interfaces;

interface IdentityUserServiceInterface
{
    /**
     * Get identity user array representation.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param string $service
     *
     * @return mixed[]
     */
    public function getIdentityToArray(IdentityUserInterface $user, string $service): array;

    /**
     * Get identity user id for given service.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param string $service
     *
     * @return mixed
     */
    public function getIdentityUserId(IdentityUserInterface $user, string $service);

    /**
     * Get identity value.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param string $service
     * @param string $key
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getIdentityValue(IdentityUserInterface $user, string $service, string $key, $default = null);

    /**
     * Set identity user id for given service.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param string $service
     * @param mixed $id
     *
     * @return void
     */
    public function setIdentityUserId(IdentityUserInterface $user, string $service, $id): void;

    /**
     * Set identity value.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param string $service
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setIdentityValue(IdentityUserInterface $user, string $service, string $key, $value): void;
}
