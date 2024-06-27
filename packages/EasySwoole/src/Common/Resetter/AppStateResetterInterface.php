<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Resetter;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface AppStateResetterInterface extends HasPriorityInterface
{
    public function resetState(): void;
}
