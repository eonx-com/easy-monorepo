<?php

declare(strict_types=1);

namespace EonX\EasyCore\Helpers;

interface StringsTrimmerInterface
{
    /**
     * Returns the cleared value, excluding those specified in the list.
     *
     * @param mixed $data
     * @param string[]|null $except
     *
     * @return mixed
     */
    public function trim($data, ?array $except = null);
}
