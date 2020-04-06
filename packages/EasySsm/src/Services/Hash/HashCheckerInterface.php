<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Hash;

interface HashCheckerInterface
{
    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $params
     */
    public function checkHash(string $name, array $params): bool;

    public function checkHashes(string $hash1, string $hash2): bool;

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $params1
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $params2
     */
    public function checkHashesForParams(array $params1, array $params2): bool;
}
