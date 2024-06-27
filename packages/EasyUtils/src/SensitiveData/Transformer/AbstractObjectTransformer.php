<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Transformer;

use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

abstract class AbstractObjectTransformer implements ObjectTransformerInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
