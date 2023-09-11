<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Authorization;

use Stringable;

interface PermissionInterface extends Stringable
{
    public function getIdentifier(): string;
}
