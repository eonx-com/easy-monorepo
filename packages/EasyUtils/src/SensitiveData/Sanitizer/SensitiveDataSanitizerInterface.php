<?php
declare(strict_types=1);

namespace EonX\EasyUtils\SensitiveData\Sanitizer;

interface SensitiveDataSanitizerInterface
{
    /**
     * @template T
     *
     * @param T $data
     *
     * @return (T is object ? T|array : (T is array ? array : (T is string ? string : T)))
     */
    public function sanitize(mixed $data): mixed;
}
