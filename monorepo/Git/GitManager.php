<?php

declare(strict_types=1);

namespace EonX\EasyMonorepo\Git;

use Symfony\Component\Process\Process;

final class GitManager
{
    public function getCurrentBranch(): string
    {
        $process = new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD']);
        $process->run();

        $currentBranch = \trim($process->getOutput());

        return $currentBranch === 'HEAD' ? 'master' : $currentBranch;
    }
}
