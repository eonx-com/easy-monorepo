<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\UserInterface;

final class UserInterfaceStub implements UserInterface
{
    /**
     * @var null|int|string
     */
    private $uniqueId;

    /**
     * UserInterfaceStub constructor.
     *
     * @param null|int|string $uniqueId
     */
    public function __construct($uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    /**
     * Get user id.
     *
     * @return null|int|string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }
}
