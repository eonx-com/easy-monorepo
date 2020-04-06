<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Parameters;

use EonX\EasySsm\Services\Parameters\Data\Diff;

interface DiffResolverInterface
{
    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $remote
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $local
     */
    public function diff(array $remote, array $local): Diff;
}
