<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Implementations;

use EonX\EasyIdentity\Exceptions\NoIdentityUserIdException;
use EonX\EasyIdentity\Interfaces\IdentityServiceInterface;
use EonX\EasyIdentity\Interfaces\IdentityUserInterface;
use EonX\EasyIdentity\Interfaces\IdentityUserServiceInterface;

abstract class AbstractIdentityService implements IdentityServiceInterface
{
    /**
     * @var \EonX\EasyIdentity\Interfaces\IdentityUserServiceInterface
     */
    private $identityUserService;

    /**
     * Create abstract identity service.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserServiceInterface $identityUserService
     */
    public function __construct(IdentityUserServiceInterface $identityUserService)
    {
        $this->identityUserService = $identityUserService;
    }

    /**
     * Get identity user array representation.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return mixed[]
     */
    public function getIdentityToArray(IdentityUserInterface $user): array
    {
        return $this->identityUserService->getIdentityToArray($user, $this->getServiceName());
    }

    /**
     * Set identity user id for given service.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param mixed $id
     *
     * @return void
     */
    public function setIdentityUserId(IdentityUserInterface $user, $id): void
    {
        $this->identityUserService->setIdentityUserId($user, $this->getServiceName(), $id);
    }

    /**
     * Set identity value.
     *
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setIdentityValue(IdentityUserInterface $user, string $key, $value): void
    {
        $this->identityUserService->setIdentityValue($user, $this->getServiceName(), $key, $value);
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
     * @param \EonX\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return string
     *
     * @throws \EonX\EasyIdentity\Exceptions\NoIdentityUserIdException
     */
    protected function getIdentityUserId(IdentityUserInterface $user): string
    {
        $userId = $this->identityUserService->getIdentityUserId($user, $this->getServiceName());

        if (\is_string($userId) === true && \trim($userId) !== '') {
            return \trim($userId);
        }

        throw new NoIdentityUserIdException(\sprintf('No identity user id for service "%s"', $this->getServiceName()));
    }
}


