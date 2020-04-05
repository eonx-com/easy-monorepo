<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Hash;

interface HashCalculatorInterface
{
    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     */
    public function calculate(array $parameters): string;
}
