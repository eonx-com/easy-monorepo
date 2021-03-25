<?php

declare(strict_types=1);

namespace EonX\EasyMonorepo\Release;

use Nette\Utils\Strings;
use PharIo\Version\Version;
use Symplify\ChangelogLinker\Analyzer\IdsAnalyzer;
use Symplify\ChangelogLinker\Application\ChangelogLinkerApplication;
use Symplify\ChangelogLinker\ChangelogCleaner;
use Symplify\ChangelogLinker\ChangelogLinker;
use Symplify\ChangelogLinker\FileSystem\ChangelogFileSystem;
use Symplify\ChangelogLinker\Github\GithubApi;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Split\Git\GitManager;
use Symplify\SmartFileSystem\SmartFileSystem;

final class GenerateReleaseNotesWorker implements ReleaseWorkerInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/5KOvEb/1
     */
    private const UNRELEASED_HEADLINE_REGEX = '#\#\# Unreleased#';

    /**
     * @var \Symplify\ChangelogLinker\ChangelogCleaner
     */
    private $changelogCleaner;

    /**
     * @var \Symplify\ChangelogLinker\FileSystem\ChangelogFileSystem
     */
    private $changelogFileSystem;

    /**
     * @var \Symplify\ChangelogLinker\ChangelogLinker
     */
    private $changelogLinker;

    /**
     * @var \Symplify\ChangelogLinker\Application\ChangelogLinkerApplication
     */
    private $changelogLinkerApplication;

    /**
     * @var \Symplify\MonorepoBuilder\Split\Git\GitManager
     */
    private $gitManager;

    /**
     * @var \Symplify\ChangelogLinker\Github\GithubApi
     */
    private $githubApi;

    /**
     * @var \Symplify\ChangelogLinker\Analyzer\IdsAnalyzer
     */
    private $idsAnalyzer;

    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(
        ChangelogCleaner $changelogCleaner,
        ChangelogLinker $changelogLinker,
        ChangelogLinkerApplication $changelogLinkerApplication,
        ChangelogFileSystem $changelogFileSystem,
        GithubApi $githubApi,
        GitManager $gitManager,
        IdsAnalyzer $idsAnalyzer,
        SmartFileSystem $smartFileSystem
    ) {
        $this->changelogCleaner = $changelogCleaner;
        $this->changelogLinker = $changelogLinker;
        $this->changelogLinkerApplication = $changelogLinkerApplication;
        $this->changelogFileSystem = $changelogFileSystem;
        $this->githubApi = $githubApi;
        $this->gitManager = $gitManager;
        $this->idsAnalyzer = $idsAnalyzer;
        $this->smartFileSystem = $smartFileSystem;
    }

    public function getDescription(Version $version): string
    {
        return \sprintf('Generate release notes for version %s', $version->getVersionString());
    }

    public function work(Version $version): void
    {
        $filename = \sprintf(__DIR__ . '/../../secret/release_%s.md', $version->getVersionString());
        $currentBranch = $this->gitManager->getCurrentBranch();
        $existingContent = $this->changelogFileSystem->readChangelog();
        $id = $this->findHighestIdMergedInBranch($existingContent, $currentBranch);
        $pullRequests = $this->githubApi->getMergedPullRequestsSinceId($id ?? 491, $currentBranch);

        $content = $this->changelogLinkerApplication->createContentFromPullRequestsBySortPriority(
            $pullRequests,
            null,
            false,
            false
        );

        $content = Strings::replace(
            $content,
            self::UNRELEASED_HEADLINE_REGEX,
            \sprintf('## Changelog - %s', $version->getVersionString())
        );

        $content = $this->changelogLinker->processContentWithLinkAppends($content);
        $content = $this->changelogCleaner->processContent($content);

        $this->smartFileSystem->dumpFile($filename, $content);
    }

    private function findHighestIdMergedInBranch(string $content, string $branch): ?int
    {
        $allIdsInChangelog = $this->idsAnalyzer->getAllIdsInChangelog($content);

        if ($allIdsInChangelog === null) {
            return null;
        }

        \rsort($allIdsInChangelog);

        foreach ($allIdsInChangelog as $id) {
            $idInt = (int)$id;

            if ($this->githubApi->isPullRequestMergedToBaseBranch($idInt, $branch)) {
                return $idInt;
            }
        }

        return null;
    }
}
