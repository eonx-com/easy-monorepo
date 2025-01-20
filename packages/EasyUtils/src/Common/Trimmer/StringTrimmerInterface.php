<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Trimmer;

interface StringTrimmerInterface
{
    /**
     * @template T
     *
     * @param T $data
     * @param string[]|null $exceptKeys
     *
     * @return (T is array ? array : T)
     */
    public function trim(mixed $data, ?array $exceptKeys = null): mixed;
}
