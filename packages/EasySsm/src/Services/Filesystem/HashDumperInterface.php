<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Filesystem;

interface HashDumperInterface
{
    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     */
    public function dumpHash(string $name, array $parameters): void;
}
