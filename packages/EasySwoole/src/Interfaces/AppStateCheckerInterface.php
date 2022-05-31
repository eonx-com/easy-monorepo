<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface AppStateCheckerInterface extends HasPriorityInterface
{
    public function isApplicationStateCompromised(): bool;
}
