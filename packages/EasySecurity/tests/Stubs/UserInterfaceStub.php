<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\UserInterface;

final class UserInterfaceStub implements UserInterface
{
    private string $uniqueId;

    public function __construct(string $uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    public function getFullName(): string
    {
        return $this->uniqueId;
    }
}
