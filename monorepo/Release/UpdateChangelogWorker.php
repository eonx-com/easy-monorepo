<?php

declare(strict_types=1);

namespace EonX\EasyMonorepo\Release;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;

final class UpdateChangelogWorker implements ReleaseWorkerInterface
{
    /**
     * @var \Symplify\MonorepoBuilder\Release\Process\ProcessRunner
     */
    private $processRunner;

    public function __construct(ProcessRunner $processRunner)
    {
        $this->processRunner = $processRunner;
    }

    public function getDescription(Version $version): string
    {
        return \sprintf('Update CHANGELOG.md for %s', $version->getVersionString());
    }

    public function work(Version $version): void
    {
        $this->processRunner->run('composer changelog');
    }
}
