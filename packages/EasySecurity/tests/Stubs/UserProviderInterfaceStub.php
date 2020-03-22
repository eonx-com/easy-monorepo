<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\UserInterface;
use EonX\EasySecurity\Interfaces\UserProviderInterface;

final class UserProviderInterfaceStub implements UserProviderInterface
{
    /**
     * @var null|\EonX\EasySecurity\Interfaces\UserInterface
     */
    private $user;

    public function __construct(?UserInterface $user = null)
    {
        $this->user = $user;
    }

    /**
     * @param int|string $uniqueId
     * @param mixed[] $data
     */
    public function getUser($uniqueId, array $data): ?UserInterface
    {
        return $this->user;
    }
}
