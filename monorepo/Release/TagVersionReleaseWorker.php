<?php

declare(strict_types=1);

namespace EonX\EasyMonorepo\Release;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;
use Symplify\MonorepoBuilder\Split\Git\GitManager;
use Throwable;

final class TagVersionReleaseWorker implements ReleaseWorkerInterface
{
    /**
     * @var \Symplify\MonorepoBuilder\Split\Git\GitManager
     */
    private $gitManager;

    /**
     * @var ProcessRunner
     */
    private $processRunner;

    public function __construct(GitManager $gitManager, ProcessRunner $processRunner)
    {
        $this->gitManager = $gitManager;
        $this->processRunner = $processRunner;
    }

    public function getDescription(Version $version): string
    {
        return sprintf('Add local tag "%s"', $version->getVersionString());
    }

    public function work(Version $version): void
    {
        $cmd = \sprintf(
            'git add . && git commit -m "Prepare Release %s" && git push',
            $version->getVersionString()
        );

        try {
            $this->processRunner->run($cmd);
        } catch (Throwable $throwable) {
            // nothing to commit
        }

        $currentBranch = $this->gitManager->getCurrentBranch();
        $notesFilename = \sprintf(__DIR__ . '/../../secret/release_%s.md', $version->getVersionString());

        // Create Release in Github
        $cmd = \sprintf(
            'gh release create %s -F %s -t %s --target %s',
            $version->getVersionString(),
            $notesFilename,
            $version->getVersionString(),
            $currentBranch
        );

        $this->processRunner->run($cmd);
    }
}
