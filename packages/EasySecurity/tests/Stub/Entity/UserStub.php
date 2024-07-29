<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Entity;

use EonX\EasySecurity\Common\Entity\UserInterface;

final readonly class UserStub implements UserInterface
{
    public function __construct(
        private string $userIdentifier,
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
