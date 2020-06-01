<?php
declare(strict_types=1);

namespace EonX\EasyAwsCredentialsFinder\Helpers;

use EonX\EasyAwsCredentialsFinder\Interfaces\Helpers\ProcessRunnerInterface;
use Symfony\Component\Process\Process;

final class ProcessRunner implements ProcessRunnerInterface
{
    public function run(array $cmd): string
    {
        $process = new Process($cmd);
        $process->mustRun();

        return $process->getOutput();
    }
}
