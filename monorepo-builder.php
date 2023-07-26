<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyMonorepo\Git\GitManager;
use EonX\EasyMonorepo\Release\PackagesListInReadmeReleaseWorker;
use EonX\EasyMonorepo\Release\PushNextDevReleaseWorker;
use EonX\EasyMonorepo\Release\TagVersionReleaseWorker;
use EonX\EasyMonorepo\Release\UpdateTagInGithubWorkflow;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\AddTagToChangelogReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileSystem;

require_once __DIR__ . '/vendor/autoload.php';

return static function (MBConfig $monorepoBuilderConfig): void {
    $monorepoBuilderConfig->packageDirectories([__DIR__ . '/packages']);
    $monorepoBuilderConfig->packageDirectoriesExcludes([]);
    $monorepoBuilderConfig->workers([
        AddTagToChangelogReleaseWorker::class,
        UpdateTagInGithubWorkflow::class,
        PackagesListInReadmeReleaseWorker::class,
        SetCurrentMutualDependenciesReleaseWorker::class,
        TagVersionReleaseWorker::class,
        SetNextMutualDependenciesReleaseWorker::class,
        UpdateBranchAliasReleaseWorker::class,
        PushNextDevReleaseWorker::class,
    ]);

    $services = $monorepoBuilderConfig->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->public();

    $services->set(ClientInterface::class, Client::class);
    $services->set(FinderSanitizer::class);
    $services->set(GitManager::class);
    $services->set(SmartFileSystem::class);
};
