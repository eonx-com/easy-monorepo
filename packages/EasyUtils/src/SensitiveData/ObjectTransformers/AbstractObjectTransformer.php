<?php

declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\ObjectTransformers;

use EonX\EasyUtils\SensitiveData\ObjectTransformerInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractObjectTransformer implements ObjectTransformerInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
