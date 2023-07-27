<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\UserInterface;

final class UserInterfaceStub implements UserInterface
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
