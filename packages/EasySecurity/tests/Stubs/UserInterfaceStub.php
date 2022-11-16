<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\UserInterface;

final class UserInterfaceStub implements UserInterface
{
    public function __construct(private string $userIdentifier)
    {
    }

    /**
     * @deprecated Will be removed in 5.0.0
     */
    public function getUniqueId(): null|int|string
    {
        return $this->userIdentifier;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
