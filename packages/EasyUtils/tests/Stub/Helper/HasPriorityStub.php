<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Stub\Helper;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;
use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

final class HasPriorityStub implements HasPriorityInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
