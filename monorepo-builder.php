<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyMonorepo\Helper\GitHelper;
use EonX\EasyMonorepo\ReleaseWorker\PackagesListInReadmeReleaseWorker;
use EonX\EasyMonorepo\ReleaseWorker\PushNextDevReleaseWorker;
use EonX\EasyMonorepo\ReleaseWorker\TagVersionReleaseWorker;
use EonX\EasyMonorepo\ReleaseWorker\UpdateTagInGithubWorkflowReleaseWorker;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\AddTagToChangelogReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileSystem;

require_once __DIR__ . '/vendor/autoload.php';

return static function (MBConfig $monorepoBuilderConfig): void {
    MBConfig::disableDefaultWorkers();

    $monorepoBuilderConfig->packageDirectories([__DIR__ . '/packages']);
    $monorepoBuilderConfig->packageDirectoriesExcludes([]);
    $monorepoBuilderConfig->workers([
        AddTagToChangelogReleaseWorker::class,
        UpdateTagInGithubWorkflowReleaseWorker::class,
        PackagesListInReadmeReleaseWorker::class,
        SetCurrentMutualDependenciesReleaseWorker::class,
        TagVersionReleaseWorker::class,
        SetNextMutualDependenciesReleaseWorker::class,
        UpdateBranchAliasReleaseWorker::class,
        PushNextDevReleaseWorker::class,
        PushTagReleaseWorker::class,
    ]);

    $services = $monorepoBuilderConfig->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->public();

    $services->set(ClientInterface::class, Client::class);
    $services->set(FinderSanitizer::class);
    $services->set(GitHelper::class);
    $services->set(SmartFileSystem::class);
};
