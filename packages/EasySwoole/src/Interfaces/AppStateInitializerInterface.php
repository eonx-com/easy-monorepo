<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface AppStateInitializerInterface extends HasPriorityInterface
{
    public function initState(): void;
}
