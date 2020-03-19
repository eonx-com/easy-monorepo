<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface ProviderInterface
{
    /**
     * @return null|int|string
     */
    public function getUniqueId();
}
