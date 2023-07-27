<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Stubs;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

final class HasPriorityStub implements HasPriorityInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
