<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\ReleaseWorker;

use EonX\EasyMonorepo\Helper\GitHelper;
use GuzzleHttp\ClientInterface;
use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;
use Throwable;

final readonly class TagVersionReleaseWorker implements ReleaseWorkerInterface
{
    public function __construct(
        private GitHelper $gitManager,
        private ClientInterface $httpClient,
        private ProcessRunner $processRunner,
    ) {
    }

    public function getDescription(Version $version): string
    {
        return \sprintf('Add local tag "%s"', $version->getVersionString());
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
        } catch (Throwable) {
            // Nothing to commit
        }

        // Create Release in GitHub
        $url = 'https://api.github.com/repos/eonx-com/easy-monorepo/releases';
        $options = [
            'body' => \json_encode([
                'draft' => false,
                'generate_release_notes' => true,
                'make_latest' => 'legacy',
                'name' => $version->getVersionString(),
                'prerelease' => false,
                'tag_name' => $version->getVersionString(),
                'target_commitish' => $this->gitManager->getCurrentBranch(),
            ]),
            'headers' => [
                'accept' => 'application/vnd.github.v3+json',
                'authorization' => \sprintf('Token %s', \getenv('GITHUB_TOKEN')),
            ],
        ];

        $this->httpClient->request('POST', $url, $options);
    }
}
