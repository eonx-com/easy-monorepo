<?php

declare(strict_types=1);

namespace EonX\EasyMonorepo\Release;

use PharIo\Version\Version;
use Symfony\Component\Yaml\Yaml;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\SmartFileSystem\SmartFileSystem;

final class UpdateTagInGithubWorkflow implements ReleaseWorkerInterface
{
    /**
     * @var string
     */
    private const WORKFLOW_FILENAME = __DIR__ . '/../../.github/workflows/split_packages.yml';

    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(SmartFileSystem $smartFileSystem)
    {
        $this->smartFileSystem = $smartFileSystem;
    }

    public function getDescription(Version $version): string
    {
        return \sprintf('Replace tag in github workflow to %s', $version->getVersionString());
    }

    public function work(Version $version): void
    {
        $workflow = Yaml::parseFile(self::WORKFLOW_FILENAME);
        $workflow['jobs']['split_packages']['strategy']['matrix']['tag'][0] = $version->getVersionString();

        $newWorkflowContent = Yaml::dump(
            $workflow,
            \PHP_INT_MAX,
            4,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );

        $newWorkflowContent = \str_replace("'on':", 'on:', $newWorkflowContent);

        $this->smartFileSystem->dumpFile(self::WORKFLOW_FILENAME, $newWorkflowContent);
    }
}
