<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

interface SensitiveDataSanitizerInterface
{
    public function sanitize(mixed $data): mixed;
}
