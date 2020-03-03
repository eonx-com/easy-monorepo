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

    /**
     * UserProviderInterfaceStub constructor.
     *
     * @param null|\EonX\EasySecurity\Interfaces\UserInterface $user
     */
    public function __construct(?UserInterface $user = null)
    {
        $this->user = $user;
    }

    /**
     * Get user for given uniqueId and data.
     *
     * @param int|string $uniqueId
     * @param mixed[] $data
     *
     * @return null|\EonX\EasySecurity\Interfaces\UserInterface
     */
    public function getUser($uniqueId, array $data): ?UserInterface
    {
        return $this->user;
    }
}
