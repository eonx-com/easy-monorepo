<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface ProviderInterface
{
    public function getUniqueId(): int|string;
}
