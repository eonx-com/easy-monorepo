<?php

declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Interfaces\Helpers;

interface ProcessRunnerInterface
{
    /**
     * @param string[] $cmd
     */
    public function run(array $cmd): string;
}
