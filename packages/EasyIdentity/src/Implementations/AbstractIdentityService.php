<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Implementations;

use LoyaltyCorp\EasyIdentity\Exceptions\NoIdentityUserIdException;
use LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceInterface;
use LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface;

abstract class AbstractIdentityService implements IdentityServiceInterface
{
    /**
     * Get identity user array representation.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return mixed[]
     */
    public function getIdentityToArray(IdentityUserInterface $user): array
    {
        return $user->getIdentityToArray($this->getServiceName());
    }

    /**
     * Set identity user id for given service.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param mixed $id
     *
     * @return void
     */
    public function setIdentityUserId(IdentityUserInterface $user, $id): void
    {
        $user->setIdentityUserId($this->getServiceName(), $id);
    }

    /**
     * Set identity value.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setIdentityValue(IdentityUserInterface $user, string $key, $value): void
    {
        $user->setIdentityValue($this->getServiceName(), $key, $value);
    }

    /**
     * Get service name.
     *
     * @return string
     */
    abstract protected function getServiceName(): string;

    /**
     * Get identity user id.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return string
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\NoIdentityUserIdException
     */
    protected function getIdentityUserId(IdentityUserInterface $user): string
    {
        $userId = $user->getIdentityUserId($this->getServiceName());

        if (empty($userId) === false) {
            return $userId;
        }

        throw new NoIdentityUserIdException(\sprintf('No identity user id for service "%s"', $this->getServiceName()));
    }
}

\class_alias(
    AbstractIdentityService::class,
    'StepTheFkUp\EasyIdentity\Implementations\AbstractIdentityService',
    false
);
