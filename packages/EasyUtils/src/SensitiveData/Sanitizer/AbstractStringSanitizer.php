<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

abstract class AbstractStringSanitizer implements StringSanitizerInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }

    /**
     * Escapes the mask pattern for safe use inside a preg_replace() replacement string, where
     * "$" and "\" would otherwise be interpreted as back-references / escapes
     */
    protected function escapeForReplacement(string $maskPattern): string
    {
        return \strtr($maskPattern, ['\\' => '\\\\', '$' => '\\$']);
    }
}
