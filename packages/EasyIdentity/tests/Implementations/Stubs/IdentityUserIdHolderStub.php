<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Tests\Implementations\Stubs;

use EonX\EasyIdentity\Interfaces\IdentityUserIdHolderInterface;

final class IdentityUserIdHolderStub implements IdentityUserIdHolderInterface
{
    /**
     * @var string
     */
    private $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function getIdentityUserId(): string
    {
        return $this->userId;
    }

    public function setIdentityUserId(string $userId): void
    {
        $this->userId = $userId;
    }
}
