<?php

declare(strict_types=1);

namespace EonX\EasyMonorepo\Release;

use EonX\EasyMonorepo\Git\GitManager;
use GuzzleHttp\ClientInterface;
use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;
use Throwable;

final class TagVersionReleaseWorker implements ReleaseWorkerInterface
{
    public function __construct(
        private readonly GitManager $gitManager,
        private readonly ClientInterface $httpClient,
        private readonly ProcessRunner $processRunner
    ) {
        // The body is not required
    }

    public function getDescription(Version $version): string
    {
        return sprintf('Add local tag "%s"', $version->getVersionString());
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function work(Version $version): void
    {
        // Commit changes, push them, create tag and push tag
        $cmd = \sprintf(
            'git add . && git commit -m "Prepare Release %s" && git push && git tag %s && git push --tags',
            $version->getVersionString(),
            $version->getVersionString()
        );

        try {
            $this->processRunner->run($cmd);
        } catch (Throwable $throwable) {
            // nothing to commit
        }

        // Create Release in GitHub
        $url = 'https://api.github.com/repos/eonx-com/easy-monorepo/releases';
        $options = [
            'headers' => [
                'accept' => 'application/vnd.github.v3+json',
                'authorization' => \sprintf('Token %s', \getenv('GITHUB_TOKEN')),
            ],
            'body' => \json_encode([
                'name' => $version->getVersionString(),
                'draft' => false,
                'prerelease' => false,
                'generate_release_notes' => true,
                'make_latest' => 'legacy',
                'tag_name' => $version->getVersionString(),
                'target_commitish' => $this->gitManager->getCurrentBranch(),
            ]),
        ];

        $this->httpClient->request('POST', $url, $options);
    }
}
