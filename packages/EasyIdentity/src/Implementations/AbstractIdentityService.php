<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Implementations;

use LoyaltyCorp\EasyIdentity\Exceptions\NoIdentityUserIdException;
use LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceInterface;
use LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface;
use LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserServiceInterface;

abstract class AbstractIdentityService implements IdentityServiceInterface
{
    /**
     * @var \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserServiceInterface
     */
    private $identityUserService;

    /**
     * Create abstract identity service.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserServiceInterface $identityUserService
     */
    public function __construct(IdentityUserServiceInterface $identityUserService)
    {
        $this->identityUserService = $identityUserService;
    }

    /**
     * Get identity user array representation.
     *
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
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
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
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
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
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
     * @param \LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface $user
     *
     * @return string
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\NoIdentityUserIdException
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

\class_alias(
    AbstractIdentityService::class,
    \StepTheFkUp\EasyIdentity\Implementations\AbstractIdentityService::class,
    false
);
