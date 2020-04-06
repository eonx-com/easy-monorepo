<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Filesystem;

interface SsmParametersDumperInterface
{
    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     */
    public function dumpParameters(string $filename, array $parameters): void;
}
