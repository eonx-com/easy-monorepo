<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface StringSanitizerInterface extends HasPriorityInterface
{
    /**
     * @param string[] $keysToMask
     */
    public function sanitizeString(string $string, string $maskPattern, array $keysToMask): string;
}
