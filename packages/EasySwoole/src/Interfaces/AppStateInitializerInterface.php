<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Interfaces;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface AppStateInitializerInterface extends HasPriorityInterface
{
    public function initState(): void;
}
