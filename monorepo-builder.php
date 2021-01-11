<?php

declare(strict_types=1);

use EonX\EasyMonorepo\Release\PackagesListInReadmeReleaseWorker;
use EonX\EasyMonorepo\Release\PushNextDevReleaseWorker;
use EonX\EasyMonorepo\Release\TagVersionReleaseWorker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\AddTagToChangelogReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\MonorepoBuilder\Split\ValueObject\ConvertFormat;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::DATA_TO_APPEND, [
        'require-dev' => [
            'phpstan/phpstan' => '^0.12.42',
            'sensiolabs/security-checker' => '^5.0',
        ],
    ]);

    $parameters->set(Option::DIRECTORIES_TO_REPOSITORIES_CONVERT_FORMAT, ConvertFormat::PASCAL_CASE_TO_KEBAB_CASE);
    $parameters->set(Option::DIRECTORIES_TO_REPOSITORIES, [
        'packages/*' => 'git@github.com:eonx-com/*.git',
    ]);

    $services = $containerConfigurator->services();

    # release workers - in order to execute
    $services->set(PackagesListInReadmeReleaseWorker::class);
    $services->set(SetCurrentMutualDependenciesReleaseWorker::class);
    $services->set(AddTagToChangelogReleaseWorker::class);
    $services->set(TagVersionReleaseWorker::class);
    $services->set(PushTagReleaseWorker::class);
    $services->set(SetNextMutualDependenciesReleaseWorker::class);
    $services->set(UpdateBranchAliasReleaseWorker::class);
    $services->set(PushNextDevReleaseWorker::class);
};
