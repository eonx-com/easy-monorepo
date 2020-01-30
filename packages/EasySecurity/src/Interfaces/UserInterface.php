<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface UserInterface
{
    /**
     * Get user id.
     *
     * @return null|int|string
     */
    public function getUniqueId();
}
