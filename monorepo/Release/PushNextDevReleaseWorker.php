<?php

declare(strict_types=1);

namespace EonX\EasyMonorepo\Release;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;
use Symplify\MonorepoBuilder\Utils\VersionUtils;

final class PushNextDevReleaseWorker implements ReleaseWorkerInterface
{
    public function __construct(
        private ProcessRunner $processRunner,
        private VersionUtils $versionUtils,
    ) {
    }

    public function getDescription(Version $version): string
    {
        $versionInString = $this->getVersionDev($version);

        return sprintf('Push "%s" open to remote repository', $versionInString);
    }

    public function work(Version $version): void
    {
        $versionInString = $this->getVersionDev($version);

        $gitAddCommitCommand = \sprintf(
            'git add . && git commit --allow-empty -m "[Release] %s and Open %s" && git push',
            $version->getVersionString(),
            $versionInString
        );

        $this->processRunner->run($gitAddCommitCommand);
    }

    private function getVersionDev(Version $version): string
    {
        return $this->versionUtils->getNextAliasFormat($version);
    }
}
