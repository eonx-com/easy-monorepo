<?php
declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Interfaces\Helpers;

interface ProcessRunnerInterface
{
    public function run(array $cmd): string;
}
