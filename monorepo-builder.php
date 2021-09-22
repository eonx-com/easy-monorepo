<?php

declare(strict_types=1);

use EonX\EasyMonorepo\Release\GenerateReleaseNotesWorker;
use EonX\EasyMonorepo\Release\PackagesListInReadmeReleaseWorker;
use EonX\EasyMonorepo\Release\PushNextDevReleaseWorker;
use EonX\EasyMonorepo\Release\TagVersionReleaseWorker;
use EonX\EasyMonorepo\Release\UpdateChangelogWorker;
use EonX\EasyMonorepo\Release\UpdateTagInGithubWorkflow;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\ChangelogLinker\ValueObject\Option as ChangelogLinkerOption;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\AddTagToChangelogReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\MonorepoBuilder\Split\ValueObject\ConvertFormat;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Try to hack something
    $override = <<<PHP
<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Testing\ComposerJson;

use Symplify\MonorepoBuilder\ValueObject\Section;

final class ComposerVersionManipulator
{
    /**
     * @param mixed[] $packageComposerJson
     * @param string[] $usedPackageNames
     * @return mixed[]
     */
    public function setAsteriskVersionForUsedPackages(array $packageComposerJson, array $usedPackageNames): array
    {
        foreach ([Section::REQUIRE, Section::REQUIRE_DEV] as $section) {
            foreach ($usedPackageNames as $usedPackageName) {
                if (! isset($packageComposerJson[$section][$usedPackageName])) {
                    continue;
                }

                $packageComposerJson[$section][$usedPackageName] = 'dev-master';
            }
        }

        return $packageComposerJson;
    }
}
PHP;

    $filename = __DIR__ . '/vendor/symplify/monorepo-builder/packages/testing/src/ComposerJson/ComposerVersionManipulator.php';
    (new \Symfony\Component\Filesystem\Filesystem())->dumpFile($filename, $override);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(ChangelogLinkerOption::AUTHORS_TO_IGNORE, ['natepage']);
    $parameters->set(ChangelogLinkerOption::NAMES_TO_URLS, []);
    $parameters->set(ChangelogLinkerOption::PACKAGE_ALIASES, []);
    $parameters->set('env(GITHUB_TOKEN)', null);
    $parameters->set(ChangelogLinkerOption::GITHUB_TOKEN, '%env(GITHUB_TOKEN)%');
    $parameters->set(ChangelogLinkerOption::REPOSITORY_NAME, 'eonx-com/easy-monorepo');
    $parameters->set(ChangelogLinkerOption::REPOSITORY_URL, 'https://github.com/eonx-com/easy-monorepo');

    $parameters->set(Option::DIRECTORIES_TO_REPOSITORIES_CONVERT_FORMAT, ConvertFormat::PASCAL_CASE_TO_KEBAB_CASE);
    $parameters->set(Option::DIRECTORIES_TO_REPOSITORIES, [
        'packages/*' => 'git@github.com:eonx-com/*.git',
    ]);

    $services = $containerConfigurator->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->public();

    $services->load('Symplify\\ChangelogLinker\\', __DIR__ . '/vendor/symplify/changelog-linker/src')
        ->exclude([
            __DIR__ . '/vendor/symplify/changelog-linker/src/HttpKernel',
            __DIR__ . '/vendor/symplify/changelog-linker/src/DependencyInjection/CompilerPass',
            __DIR__ . '/vendor/symplify/changelog-linker/src/Exception',
            __DIR__ . '/vendor/symplify/changelog-linker/src/ValueObject',
        ]);

    $services->set(ClientInterface::class, Client::class);

    # release workers - in order to execute
    $services->set(GenerateReleaseNotesWorker::class);
    $services->set(UpdateChangelogWorker::class);
    $services->set(AddTagToChangelogReleaseWorker::class);
    $services->set(UpdateTagInGithubWorkflow::class);
    $services->set(PackagesListInReadmeReleaseWorker::class);
    $services->set(SetCurrentMutualDependenciesReleaseWorker::class);
    $services->set(TagVersionReleaseWorker::class);
    $services->set(SetNextMutualDependenciesReleaseWorker::class);
    $services->set(UpdateBranchAliasReleaseWorker::class);
    $services->set(PushNextDevReleaseWorker::class);
};
