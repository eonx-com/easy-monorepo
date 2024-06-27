<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\ValueObject;

use Stringable;

interface PermissionInterface extends Stringable
{
    public function getIdentifier(): string;
}
