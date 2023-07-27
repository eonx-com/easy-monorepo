<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\StringSanitizers;

use EonX\EasyUtils\SensitiveData\StringSanitizerInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractStringSanitizer implements StringSanitizerInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
