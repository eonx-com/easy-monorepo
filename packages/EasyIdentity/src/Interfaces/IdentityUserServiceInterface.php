<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Interfaces;

interface IdentityUserServiceInterface
{
    /**
     * @return mixed[]
     */
    public function getIdentityToArray(IdentityUserInterface $user, string $service): array;

    /**
     * @return mixed
     */
    public function getIdentityUserId(IdentityUserInterface $user, string $service);

    /**
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getIdentityValue(IdentityUserInterface $user, string $service, string $key, $default = null);

    /**
     * @param mixed $id
     */
    public function setIdentityUserId(IdentityUserInterface $user, string $service, $id): void;

    /**
     * @param mixed $value
     */
    public function setIdentityValue(IdentityUserInterface $user, string $service, string $key, $value): void;
}
