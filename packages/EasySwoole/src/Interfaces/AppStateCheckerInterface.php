<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Interfaces;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface AppStateCheckerInterface extends HasPriorityInterface
{
    public function isApplicationStateCompromised(): bool;
}
