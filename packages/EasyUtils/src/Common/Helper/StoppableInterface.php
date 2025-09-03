<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Helper;

interface StoppableInterface
{
    public function isPropagationStopped(): bool;

    public function stopPropagation(): void;
}
