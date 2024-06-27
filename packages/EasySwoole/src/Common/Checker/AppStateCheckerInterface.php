<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Checker;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface AppStateCheckerInterface extends HasPriorityInterface
{
    public function isApplicationStateCompromised(): bool;
}
