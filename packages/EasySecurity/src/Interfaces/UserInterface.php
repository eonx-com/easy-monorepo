<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface UserInterface
{
    /**
     * @return null|int|string
     */
    public function getUniqueId();
}
