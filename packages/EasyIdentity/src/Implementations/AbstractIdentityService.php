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

    public function __construct(IdentityUserServiceInterface $identityUserService)
    {
        $this->identityUserService = $identityUserService;
    }

    /**
     * @return mixed[]
     */
    public function getIdentityToArray(IdentityUserInterface $user): array
    {
        return $this->identityUserService->getIdentityToArray($user, $this->getServiceName());
    }

    /**
     * @param mixed $id
     */
    public function setIdentityUserId(IdentityUserInterface $user, $id): void
    {
        $this->identityUserService->setIdentityUserId($user, $this->getServiceName(), $id);
    }

    /**
     * @param mixed $value
     */
    public function setIdentityValue(IdentityUserInterface $user, string $key, $value): void
    {
        $this->identityUserService->setIdentityValue($user, $this->getServiceName(), $key, $value);
    }

    abstract protected function getServiceName(): string;

    protected function getIdentityUserId(IdentityUserInterface $user): string
    {
        $userId = $this->identityUserService->getIdentityUserId($user, $this->getServiceName());

        if (\is_string($userId) === true && \trim($userId) !== '') {
            return \trim($userId);
        }

        throw new NoIdentityUserIdException(\sprintf('No identity user id for service "%s"', $this->getServiceName()));
    }
}
