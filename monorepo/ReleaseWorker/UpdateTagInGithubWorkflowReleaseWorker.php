<?php
declare(strict_types=1);

namespace EonX\EasyMonorepo\ReleaseWorker;

use PharIo\Version\Version;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

final readonly class UpdateTagInGithubWorkflowReleaseWorker implements ReleaseWorkerInterface
{
    private const WORKFLOW_FILENAME = __DIR__ . '/../../.github/workflows/split_packages.yml';

    private Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function getDescription(Version $version): string
    {
        return \sprintf('Replace tag in github workflow to %s', $version->getVersionString());
    }

    public function work(Version $version): void
    {
        /** @var array $workflow */
        $workflow = Yaml::parseFile(self::WORKFLOW_FILENAME);
        $workflow['jobs']['split_packages']['strategy']['matrix']['tag'][0] = $version->getVersionString();

        $newWorkflowContent = Yaml::dump(
            $workflow,
            \PHP_INT_MAX,
            4,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );

        $newWorkflowContent = \str_replace("'on':", 'on:', $newWorkflowContent);

        $this->filesystem->dumpFile(self::WORKFLOW_FILENAME, $newWorkflowContent);
    }
}
