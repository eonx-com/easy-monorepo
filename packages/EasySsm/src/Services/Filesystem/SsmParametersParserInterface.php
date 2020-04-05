<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Filesystem;

interface SsmParametersParserInterface
{
    /**
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     *
     * @throws \EonX\EasySsm\Services\Filesystem\Exceptions\InvalidTagException
     */
    public function parseParameters(string $filename): array;
}
