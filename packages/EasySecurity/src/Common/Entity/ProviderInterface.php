<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Entity;

interface ProviderInterface
{
    public function getUniqueId(): int|string;
}
