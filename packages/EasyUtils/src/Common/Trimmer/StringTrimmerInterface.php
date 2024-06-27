<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Trimmer;

interface StringTrimmerInterface
{
    /**
     * @param string[]|null $exceptKeys
     */
    public function trim(mixed $data, ?array $exceptKeys = null): mixed;
}
