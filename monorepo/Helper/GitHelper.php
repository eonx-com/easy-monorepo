<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\Helper;

use Symfony\Component\Process\Process;

final class GitHelper
{
    public function getCurrentBranch(): string
    {
        $process = new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD']);
        $process->run();

        $currentBranch = \trim($process->getOutput());
        $currentSha = $this->getCurrentSha();

        return $currentBranch === 'HEAD' ? $currentSha : $currentBranch;
    }

    private function getCurrentSha(): string
    {
        $sha = \getenv('GITHUB_SHA');

        return \is_string($sha) ? $sha : 'invalid-sha';
    }
}
