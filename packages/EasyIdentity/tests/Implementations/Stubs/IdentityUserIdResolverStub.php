<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Tests\Implementations\Stubs;

use StepTheFkUp\EasyIdentity\Interfaces\IdentityUserIdResolverInterface;

final class IdentityUserIdResolverStub implements IdentityUserIdResolverInterface
{
    /**
     * @var string
     */
    private $userId;

    /**
     * IdentityUserIdResolverStub constructor.
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
    public function getUserId(): string
    {
        return $this->userId;
    }
}
