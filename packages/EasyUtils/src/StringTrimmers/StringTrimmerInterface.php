<?php

declare(strict_types=1);

namespace EonX\EasyUtils\StringTrimmers;

interface StringTrimmerInterface
{
    /**
     * @param mixed[]|string $data
     * @param string[]|null $exceptKeys
     *
     * @return mixed[]|string
     */
    public function trim(mixed $data, ?array $exceptKeys = null): mixed;
}
