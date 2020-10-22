<?php

declare(strict_types=1);

use Nette\Utils\Strings;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use Symplify\SmartFileSystem\SmartFileInfo;

require __DIR__ . '/../vendor/autoload.php';

// prevents forgetting adding a new package to test suite of split packages https://github.com/eonx-com/easy-monorepo/pull/406/files#diff-b2f7022f187f743fd469583e9ae2a4b6f1a681e5076f0569d1c40342b5911cc9

$symfonyStyleFactory = new SymfonyStyleFactory();
$symfonyStyle = $symfonyStyleFactory->create();

// 1. find all packages with tests
$finder = new Finder();
$finder->directories()
    ->in(__DIR__ . '/../packages')
    ->name('#tests#');

$testAwarePackagesNames = [];
foreach ($finder->getIterator() as $fileInfo) {
    $filePath = $fileInfo->getRealPath();
    $matches = Strings::match($filePath, '#packages\/(?<package_name>[A-Z]\w+)\/tests#');
    if (! isset($matches['package_name'])) {
        continue;
    }

    $testAwarePackagesNames[] = $matches['package_name'];
}

$message = sprintf('Found %d packages with tests', count($testAwarePackagesNames));
$symfonyStyle->note($message);

// 2. check split test workflow it mentions them all
$splitWorkflowFileInfo = new SmartFileInfo(__DIR__ . '/../.github/workflows/split_tests.yml');
$splitWorkflowFileContent = $splitWorkflowFileInfo->getContents();

$missingTestAwarePackageNames = [];
foreach ($testAwarePackagesNames as $testAwarePackageName) {
    $packageNameItemPattern = '#\-\s+' . preg_quote($testAwarePackageName, '#') . '\b#';

    $symfonyStyle->note(sprintf('Checking "%s" split package', $testAwarePackageName));
    if (Strings::match($splitWorkflowFileContent, $packageNameItemPattern)) {
        $symfonyStyle->success('Found!');
        continue;
    }

    $missingTestAwarePackageNames[] = $testAwarePackageName;
}

$symfonyStyle->newLine(2);

if ($missingTestAwarePackageNames === []) {
    $message = sprintf('All packages were found in "%s" file', $splitWorkflowFileInfo->getRelativeFilePathFromCwd());
    $symfonyStyle->success($message);
    exit(ShellCode::SUCCESS);
}

// 3. report those not found
$errorMessage = sprintf('%d packages were not found in "%s" file - complete them', count($missingTestAwarePackageNames), $splitWorkflowFileInfo->getRelativeFilePathFromCwd());
$symfonyStyle->error($errorMessage);
$symfonyStyle->listing($missingTestAwarePackageNames);

exit(ShellCode::ERROR);
