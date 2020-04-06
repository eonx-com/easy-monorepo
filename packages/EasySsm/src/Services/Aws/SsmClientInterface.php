<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Aws;

use EonX\EasySsm\Services\Parameters\Data\Diff;

interface SsmClientInterface
{
    public function applyDiff(Diff $diff): void;

    /**
     * @return \EonX\EasySsm\Services\Aws\Data\SsmParameter[]
     */
    public function getAllParameters(?string $path = null): array;
}
