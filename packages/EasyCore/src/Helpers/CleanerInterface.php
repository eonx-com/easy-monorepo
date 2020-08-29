<?php

declare(strict_types=1);

namespace EonX\EasyCore\Helpers;

interface CleanerInterface
{
    /**
     * Returns the cleared value, excluding those specified in the list.
     *
     * @param mixed $data
     * @param string[] $except
     *
     * @return mixed
     */
    public function clean($data, array $except);
}
