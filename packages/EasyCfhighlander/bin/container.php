<?php
declare(strict_types=1);

use LoyaltyCorp\EasyCfhighlander\HttpKernel\CfhighlanderKernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\PackageBuilder\Configuration\ConfigFileFinder;

$configName = 'easy-cfhighlander.yaml';
$configFallback = ['easy-cfhighlander.yml'];
$configs = [];

// Get config
ConfigFileFinder::detectFromInput($configName, new ArgvInput());
$configs[] = ConfigFileFinder::provide($configName, $configFallback);

$kernel = new CfhighlanderKernel();
$kernel->setConfigs(\array_filter($configs));
$kernel->boot();

return $kernel->getContainer();
