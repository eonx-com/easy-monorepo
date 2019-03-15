<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Tests\Implementations\Stubs;

use StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdHolderInterface;

final class IdentityUserIdHolderStub implements IdentityUserIdHolderInterface
{
    /**
     * @var string
     */
    private $userId;

    /**
     * IdentityUserIdHolderStub constructor.
     *
     * @param string $userId
     */
    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Resolve the identity user id.
     *
     * @return string
     */
    public function getIdentityUserId(): string
    {
        return $this->userId;
    }

    /**
     * Set the identity user id.
     *
     * @param string $userId
     *
     * @return void
     */
    public function setIdentityUserId(string $userId): void
    {
        $this->userId = $userId;
    }
}
