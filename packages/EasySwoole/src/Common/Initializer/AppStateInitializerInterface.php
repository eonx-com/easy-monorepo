<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Initializer;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface AppStateInitializerInterface extends HasPriorityInterface
{
    public function initState(): void;
}
