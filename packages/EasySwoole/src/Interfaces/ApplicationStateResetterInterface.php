<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

interface ApplicationStateResetterInterface extends HasPriorityInterface
{
    public function resetState(): void;
}
