<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Interfaces;

interface ResolverInterface
{
    public function getPriority(): int;
}
