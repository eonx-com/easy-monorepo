<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Interfaces;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface AppStateResetterInterface extends HasPriorityInterface
{
    public function resetState(): void;
}
